<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MovieResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'movie_id' => $this->movie_id,
            'title' => $this->title,
            'content_type' => $this->content_type,
            'genre_primary' => $this->genre_primary,
            'genre_secondary' => $this->genre_secondary,
            'release_year' => $this->release_year,
            'duration_minutes' => $this->duration_minutes,
            'rating' => $this->rating,
            'language' => $this->language,
            'country_of_origin' => $this->country_of_origin,
            'imdb_rating' => $this->imdb_rating,
            'production_budget' => $this->production_budget,
            'box_office_revenue' => $this->box_office_revenue,
            'number_of_seasons' => $this->number_of_seasons,
            'number_of_episodes' => $this->number_of_episodes,
            'is_netflix_original' => $this->is_netflix_original,
            'added_to_platform' => $this->added_to_platform?->format('Y-m-d'),
            'content_warning' => $this->content_warning,
            'average_rating' => $this->when($this->relationLoaded('reviews'), function () {
                return round($this->averageRating(), 2);
            }),
            'review_count' => $this->when($this->relationLoaded('reviews'), function () {
                return $this->reviewCount();
            }),
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
            'reviewers' => UserResource::collection($this->whenLoaded('reviewers')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
