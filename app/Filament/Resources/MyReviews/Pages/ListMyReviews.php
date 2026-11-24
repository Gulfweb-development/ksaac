<?php

namespace App\Filament\Resources\MyReviews\Pages;

use App\Filament\Resources\MyReviews\MyReviewResource;
use Filament\Resources\Pages\ListRecords;

class ListMyReviews extends ListRecords
{
    protected static string $resource = MyReviewResource::class;
}
