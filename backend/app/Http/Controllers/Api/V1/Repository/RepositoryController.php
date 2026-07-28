<?php

namespace App\Http\Controllers\Api\V1\Repository;

use App\Http\Controllers\Controller;
use App\Http\Requests\Repository\StoreRepositoryRequest;
use App\Http\Requests\Repository\UpdateRepositoryRequest;
use App\Http\Resources\Repository\RepositoryResource;
use App\Services\Repository\RepositoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class RepositoryController extends Controller
{
    public function __construct(
        private readonly RepositoryService $repositoryService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return RepositoryResource::collection($this->repositoryService->getAll($request->user()->id));
    }

    public function store(StoreRepositoryRequest $request): JsonResponse
    {
        $repository = $this->repositoryService->connect($request->validated('full_name'), $request->user()->id);

        return RepositoryResource::make($repository)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(UpdateRepositoryRequest $request, string $uuid): RepositoryResource
    {
        return RepositoryResource::make(
            $this->repositoryService->setActive($uuid, $request->boolean('is_active'), $request->user()->id),
        );
    }

    public function destroy(Request $request, string $uuid): Response
    {
        $this->repositoryService->delete($uuid, $request->user()->id);

        return response()->noContent();
    }
}
