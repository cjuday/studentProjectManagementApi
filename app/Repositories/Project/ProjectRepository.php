<?php

namespace App\Repositories\Project;

use App\Enums\UserRole;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ProjectRepository
{
    public function createProject(array $data): Project
    {
        return Project::create($data);
    }

    public function getProjectsForUser(User $user): Collection
    {
        return match ($user->role) {
            UserRole::Student => Project::with(['student', 'teacher'])->where('student_id', $user->id)->latest()->get(),
            UserRole::Teacher => Project::with(['student', 'teacher'])->where('teacher_id', $user->id)->latest()->get(),
            UserRole::Admin => Project::with(['student', 'teacher'])->latest()->get(),
        };
    }

    public function findProjectById(int $id): ?Project
    {
        return Project::with(['student', 'teacher'])->find($id);
    }

    public function updateProject(Project $project, array $data): Project
    {
        $project->update($data);

        return $project->fresh(['student', 'teacher']);
    }

    public function deleteProject(Project $project): bool
    {
        return $project->delete();
    }
}