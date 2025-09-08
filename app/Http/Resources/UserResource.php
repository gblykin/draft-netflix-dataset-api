<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'user_id' => $this->user_id,
            'email' => $this->email,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->first_name . ' ' . $this->last_name,
            'age' => $this->age,
            'gender' => $this->gender,
            'country' => $this->country,
            'state_province' => $this->state_province,
            'city' => $this->city,
            'subscription_plan' => $this->subscription_plan,
            'subscription_start_date' => $this->subscription_start_date?->format('Y-m-d'),
            'is_active' => $this->is_active,
            'monthly_spend' => $this->monthly_spend,
            'primary_device' => $this->primary_device,
            'household_size' => $this->household_size,
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
            'reviewed_movies' => MovieResource::collection($this->whenLoaded('reviewedMovies')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
