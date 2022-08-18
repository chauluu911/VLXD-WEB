<?php

namespace App\Services;

use App\Constant\ConfigTableName;
use App\Enums\EErrorCode;
use App\Enums\EOrderStatus;
use App\Enums\EPaymentStatus;
use App\Enums\EResourceType;
use App\Enums\EStatus;
use App\Enums\EStoreFileType;
use App\Helpers\FileUtility;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductCategoryAttribute;
use App\Models\ProductResource;
use App\Models\Review;
use App\Models\ShopResource;
use App\Repositories\ReviewRepository;
use App\Repositories\ShopResourceRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReviewService {
    private ReviewRepository $reviewRepository;

    public function __construct(ReviewRepository $reviewRepository) {
        $this->reviewRepository = $reviewRepository;
    }

    public function getByOptions(array $options) {
        return $this->reviewRepository->getByOptions($options);
    }

    public function getEvaluate($tableName, $tableId) {
        return $this->reviewRepository->getEvaluate($tableName, $tableId);
    }

    public function saveReview(array $data, int $currentUserId) {
        return DB::transaction(function() use ($data, $currentUserId) {
            $review = new Review();
            $review->table_name = Arr::get($data, 'tableName',null);
            $review->table_id = Arr::get($data, 'tableId',null);
            $review->content = Arr::get($data, 'content',null);
            $review->star = Arr::get($data, 'star',null);
            $review->status = EStatus::ACTIVE;
            $review->created_by = $currentUserId;
            $review->save();
            $evaluate = $this->getEvaluate(Arr::get($data, 'tableName',null),
                Arr::get($data, 'tableId',null));
            return [
                'error' => EErrorCode::NO_ERROR,
                'evaluate' => $evaluate
            ];
        });
    }

}
