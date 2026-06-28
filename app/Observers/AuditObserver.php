<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditObserver
{
    public function created(Model $model): void
    {
        $this->log('created', $model);
    }

    public function updated(Model $model): void
    {
        $changes = $model->getChanges();
        unset($changes['updated_at'], $changes['created_at']);

        if (empty($changes)) {
            return;
        }

        $this->log('updated', $model, $changes);
    }

    public function deleted(Model $model): void
    {
        $this->log('deleted', $model);
    }

    private function log(string $event, Model $model, array $changes = []): void
    {
        // إخفاء الحقول الحساسة
        unset($changes['password'], $changes['remember_token']);

        AuditLog::create([
            'user_id' => auth()->id(),
            'event' => $event,
            'auditable_type' => $model::class,
            'auditable_id' => $model->getKey(),
            'description' => $this->describe($model),
            'changes' => $changes ?: null,
            'created_at' => now(),
        ]);
    }

    private function describe(Model $model): string
    {
        return $model->name
            ?? $model->title
            ?? (class_basename($model) . ' #' . $model->getKey());
    }
}
