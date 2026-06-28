<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * يبني بيانات التقارير (رؤوس + صفوف) جاهزة للعرض/الطباعة/التصدير.
 */
class ReportService
{
    public const TYPES = [
        'projects' => 'تقرير المشاريع',
        'tasks' => 'تقرير المهام',
        'workload' => 'تقرير أداء الموظفين',
        'delays' => 'تقرير التأخير',
        'departments' => 'تقرير الأقسام',
    ];

    private array $status = [
        'pending' => 'قيد الانتظار', 'in_progress' => 'قيد التنفيذ',
        'completed' => 'مكتمل', 'cancelled' => 'ملغى',
    ];

    private array $priority = [
        'low' => 'منخفض', 'medium' => 'متوسط', 'high' => 'عالٍ', 'urgent' => 'عاجل',
    ];

    public function build(string $type): array
    {
        return match ($type) {
            'tasks' => $this->tasks(),
            'workload' => $this->workload(),
            'delays' => $this->delays(),
            'departments' => $this->departments(),
            default => $this->projects(),
        };
    }

    private function projects(): array
    {
        $rows = Project::with('manager')->orderByDesc('created_at')->get()->map(fn (Project $p) => [
            $p->name,
            $this->status[$p->status] ?? $p->status,
            $this->priority[$p->priority] ?? $p->priority,
            ($p->progress ?? 0) . '%',
            optional($p->manager)->name ?? '—',
            optional($p->end_date)->format('Y-m-d') ?? '—',
            $p->is_delayed ? 'نعم' : 'لا',
        ])->toArray();

        return [
            'title' => 'تقرير المشاريع',
            'headers' => ['المشروع', 'الحالة', 'الأولوية', 'الإنجاز', 'مدير المشروع', 'تاريخ النهاية', 'متأخر؟'],
            'rows' => $rows,
        ];
    }

    private function tasks(): array
    {
        $rows = Task::with(['project', 'assignedUser'])->orderByDesc('created_at')->get()->map(fn (Task $t) => [
            $t->title,
            optional($t->project)->name ?? '—',
            $this->status[$t->status] ?? $t->status,
            $this->priority[$t->priority] ?? $t->priority,
            optional($t->assignedUser)->name ?? '—',
            ($t->progress ?? 0) . '%',
            optional($t->due_date)->format('Y-m-d') ?? '—',
            $t->is_delayed ? 'نعم' : 'لا',
        ])->toArray();

        return [
            'title' => 'تقرير المهام',
            'headers' => ['المهمة', 'المشروع', 'الحالة', 'الأولوية', 'المسؤول', 'الإنجاز', 'تاريخ النهاية', 'متأخر؟'],
            'rows' => $rows,
        ];
    }

    private function workload(): array
    {
        $today = Carbon::today();

        $rows = User::with('department')->get()->map(function (User $u) use ($today) {
            $base = Task::where('assigned_to', $u->id);

            $total = (clone $base)->count();
            $inProgress = (clone $base)->where('status', 'in_progress')->count();
            $completed = (clone $base)->where('status', 'completed')->count();
            $delayed = (clone $base)->whereNotIn('status', ['completed', 'cancelled'])
                ->whereDate('due_date', '<', $today)->count();

            return [
                $u->name,
                optional($u->department)->name ?? '—',
                $u->job_title ?? '—',
                $total,
                $inProgress,
                $completed,
                $delayed,
            ];
        })->toArray();

        return [
            'title' => 'تقرير أداء الموظفين (عبء العمل)',
            'headers' => ['الموظف', 'القسم', 'المسمى الوظيفي', 'إجمالي المهام', 'قيد التنفيذ', 'مكتملة', 'متأخرة'],
            'rows' => $rows,
        ];
    }

    private function delays(): array
    {
        $today = Carbon::today();

        $rows = Task::with(['project', 'assignedUser'])
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->whereDate('due_date', '<', $today)
            ->orderBy('due_date')
            ->get()
            ->map(fn (Task $t) => [
                $t->title,
                optional($t->project)->name ?? '—',
                optional($t->assignedUser)->name ?? '—',
                optional($t->due_date)->format('Y-m-d') ?? '—',
                $t->days_delayed,
                $t->delay_reason ?: 'لم يُسجّل',
            ])->toArray();

        return [
            'title' => 'تقرير التأخير',
            'headers' => ['المهمة', 'المشروع', 'المسؤول', 'تاريخ النهاية', 'أيام التأخير', 'سبب التأخير'],
            'rows' => $rows,
        ];
    }

    private function departments(): array
    {
        $today = Carbon::today();

        $rows = Department::withCount('users')->get()->map(function (Department $d) use ($today) {
            $base = Task::where('department_id', $d->id);
            $total = (clone $base)->count();
            $completed = (clone $base)->where('status', 'completed')->count();
            $delayed = (clone $base)->whereNotIn('status', ['completed', 'cancelled'])
                ->whereDate('due_date', '<', $today)->count();

            return [
                $d->name,
                $d->users_count,
                $total,
                $completed,
                $delayed,
            ];
        })->toArray();

        return [
            'title' => 'تقرير الأقسام',
            'headers' => ['القسم', 'عدد الموظفين', 'إجمالي المهام', 'مكتملة', 'متأخرة'],
            'rows' => $rows,
        ];
    }
}
