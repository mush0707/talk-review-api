<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseApiController;
use App\Http\Controllers\Controller;
use App\Services\Tags\Data\TagSearchData;
use App\Services\Tags\TagLibraryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends BaseApiController
{
    public function __construct(
        private TagLibraryRepository $repository
    )
    {
    }

    public function index(): JsonResponse
    {
        return $this->success($this->repository->list(TagSearchData::from(request())));
    }
}
