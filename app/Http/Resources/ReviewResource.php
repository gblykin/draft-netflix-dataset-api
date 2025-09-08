<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
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
            'review_id' => $this->review_id,
            'user_id' => $this->user_id,
            'movie_id' => $this->movie_id,
            'rating' => $this->rating,
            'review_date' => $this->review_date->format('Y-m-d'),
            'device_type' => $this->device_type,
            'is_verified_watch' => $this->is_verified_watch,
            'helpful_votes' => $this->helpful_votes,
            'total_votes' => $this->total_votes,
            'helpfulness_ratio' => $this->total_votes > 0 ? round($this->helpful_votes / $this->total_votes, 2) : 0,
            'review_text' => $this->review_text,
            'sentiment' => $this->sentiment,
            'sentiment_score' => $this->sentiment_score,
            'user' => new UserResource($this->whenLoaded('user')),
            'movie' => new MovieResource($this->whenLoaded('movie')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
