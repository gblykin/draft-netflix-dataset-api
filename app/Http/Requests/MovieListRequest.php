<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MovieListRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Pagination
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
            
            // Sorting
            'sort_by' => 'sometimes|string|in:title,release_year,duration_minutes,production_budget,box_office_revenue,imdb_rating',
            'sort_direction' => 'sometimes|string|in:asc,desc',
            
            // Filtering
            'genre' => 'sometimes|string|max:255',
            'genre_primary' => 'sometimes|string|max:255',
            'genre_secondary' => 'sometimes|string|max:255',
            'content_type' => 'sometimes|string|max:255',
            'release_year' => 'sometimes|integer|min:1900|max:' . date('Y'),
            'rating' => 'sometimes|string|max:10',
            'country_of_origin' => 'sometimes|string|max:255',
            'language' => 'sometimes|string|max:255',
            'is_netflix_original' => 'sometimes|in:true,false,1,0',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'per_page.max' => 'The per page value may not be greater than 100.',
            'sort_by.in' => 'The sort by field must be one of: title, release_year, duration_minutes, production_budget, box_office_revenue, imdb_rating.',
            'sort_direction.in' => 'The sort direction must be either asc or desc.',
            'release_year.max' => 'The release year cannot be in the future.',
            'release_year.min' => 'The release year must be after 1900.',
        ];
    }
}
