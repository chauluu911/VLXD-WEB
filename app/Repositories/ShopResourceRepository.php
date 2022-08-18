<?php

namespace App\Repositories;

 use App\Enums\EDateFormat;
 use App\Models\ShopResource;
 use Illuminate\Support\Arr;

 class ShopResourceRepository extends BaseRepository {
     public function __construct(ShopResource $shopResource) {
         $this->model = $shopResource;
     }

     public function getByOptions(array $options) {
         $result = $this->model
             ->from('shop_resource as sr')
             ->select('sr.*');
         foreach ($options as $key => $val) {
             switch ($key) {
                 case 'status':
                     $result->where('sr.status', $val);
                     break;
                 case 'id':
                     $result->where('sr.id', $val);
                     break;
                 case 'shop_id':
                     $result->where('sr.shop_id', $val);
                     break;
                 case 'createdAtFrom':
                     $result->where('sr.created_at', '>=', $val->copy()->timezone(config('app.timezone'))->startOfDay()->format(EDateFormat::MODEL_DATE_FORMAT));
                     break;
                 case 'createdAtTo':
                     $result->where('sr.created_at', '<', $val->copy()->timezone(config('app.timezone'))->startOfDay()->addDay()->format(EDateFormat::MODEL_DATE_FORMAT));
             }
         }

         $orderBy = Arr::get($options,'orderBy', 'created_at');
         $orderDirection = Arr::get($options,'orderDirection', 'desc');
         switch ($orderBy) {
             default:
                 $result->orderBy("sr.$orderBy", "$orderDirection");
                 break;
         }

         return parent::getByOption($options, $result);
     }
 }
