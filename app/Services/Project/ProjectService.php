<?php

namespace App\Services\Project;

use App\Enums\ProjectStatus;
use App\Enums\UserRole;
use App\Models\Project;
use App\Models\User;
use App\Repositories\Project\ProjectRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class ProjectService
{
    protected ProjectRepository $projectRepository;

    public function __construct(ProjectRepository $projectRepository)
    {
        $this->projectRepository = $projectRepository;
    }

    public function create(array $data, User $user): Project
    {
        $data['student_id'] = $user->id;
        $data['status'] = ProjectStatus::Pending;

        return $this->projectRepository->createProject($data);
    }

    public function getProjects(User $user): Collection
    {
        return $this->projectRepository->getProjectsForUser($user);
    }

    public function findById(int $id, User $user): Project
    {
        $project = $this->projectRepository->findProjectById($id);

        if (! $project) {
            throw new ModelNotFoundException('Project not found.');
        }

        $this->ensureUserCanViewProject($project, $user);

        return $project;
    }

    public function update(int $id, array $data, User $user): Project
    {
        $project = $this->findById($id, $user);

        if ($user->role !== UserRole::Student || $project->student_id !== $user->id) {
            throw ValidationException::withMessages([
                'permission' => ['You are not allowed to update this project.'],
            ]);
        }

        return $this->projectRepository->updateProject($project, $data);
    }

    public function updateStatus(int $id, ProjectStatus $status, User $user): Project
    {
        $project = $this->findById($id, $user);

        if (! in_array($user->role, [UserRole::Teacher, UserRole::Admin], true)) {
            throw ValidationException::withMessages([
                'permission' => ['Only teacher or admin can update project status.'],
            ]);
        }

        return $this->projectRepository->updateProject($project, [
            'status' => $status,
        ]);
    }

    public function delete(int $id, User $user): bool
    {
        $project = $this->findById($id, $user);

        if ($user->role !== UserRole::Student || $project->student_id !== $user->id) {
            throw ValidationException::withMessages([
                'permission' => ['You are not allowed to delete this project.'],
            ]);
        }

        if ($project->status !== ProjectStatus::Pending) {
            throw ValidationException::withMessages([
                'status' => ['Only pending projects can be deleted.'],
            ]);
        }

        return $this->projectRepository->deleteProject($project);
    }

    private function ensureUserCanViewProject(Project $project, User $user): void
    {
        $canView = match ($user->role) {
            UserRole::Student => $project->student_id === $user->id,
            UserRole::Teacher => $project->teacher_id === $user->id,
            UserRole::Admin => true,
        };

        if (! $canView) {
            throw ValidationException::withMessages([
                'permission' => ['You are not allowed to view this project.'],
            ]);
        }
    }
}