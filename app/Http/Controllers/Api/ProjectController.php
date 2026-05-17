<?php

namespace App\Http\Controllers\Api;

use App\Enums\ProjectStatus;
use App\Http\Controllers\Controller;
use App\Services\Project\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class ProjectController extends Controller
{
    protected ProjectService $projectService;

    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    public function index(Request $request): JsonResponse
    {
        $projects = $this->projectService->getProjects($request->user());

        return response()->json([
            'message' => 'Projects fetched successfully.',
            'data' => $projects,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'repository_link' => 'required|array|min:1',
            'repository_link.*' => 'required|string|max:255',
            'demo_link' => 'nullable|url',
            'file_path' => 'nullable|file|mimes:pdf,doc,docx,zip|max:5120',
            'teacher_id' => 'required|exists:users,id',
        ]);

        if ($request->hasFile('file_path')) {
            $data['file_path'] = $request->file('file_path')->store('project-files', 'public');
        }

        $project = $this->projectService->create($data, $request->user());

        return response()->json([
            'message' => 'Project created successfully.',
            'data' => $project,
        ], 201);
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $project = $this->projectService->findById($id, $request->user());

        return response()->json([
            'message' => 'Project fetched successfully.',
            'data' => $project,
        ]);
    }

    public function update(int $id, Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'repository_link' => 'required|url',
            'demo_link' => 'nullable|url',
            'file_path' => 'nullable|string',
            'teacher_id' => 'nullable|exists:users,id',
        ]);

        $project = $this->projectService->update($id, $data, $request->user());

        return response()->json([
            'message' => 'Project updated successfully.',
            'data' => $project,
        ]);
    }

    public function updateStatus(int $id, Request $request): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', new Enum(ProjectStatus::class)],
        ]);

        $project = $this->projectService->updateStatus(
            $id,
            ProjectStatus::from($data['status']),
            $request->user()
        );

        return response()->json([
            'message' => 'Project status updated successfully.',
            'data' => $project,
        ]);
    }

    public function destroy(int $id, Request $request): JsonResponse
    {
        $this->projectService->delete($id, $request->user());

        return response()->json([
            'message' => 'Project deleted successfully.',
        ]);
    }
}