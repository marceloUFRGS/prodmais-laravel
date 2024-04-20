<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Work extends Model
{
    use HasFactory;

    protected $casts = [
        'about' => 'array',
        'author' => 'array',
        'author_array' => 'array',
        'authorLattesIds' => 'array',
        'isbn' => 'array',
        'publisher' => 'array'
    ];

    protected $fillable = [
        'about',
        'author',
        'author_array',
        'authorLattesIds',
        'datePublished',
        'doi',
        'educationEvent',
        'inLanguage',
        'isbn',
        'isPartOf',
        'issn',
        'issueNumber',
        'name',
        'pageEnd',
        'pageStart',
        'publisher',
        'type',
        'url',
        'volumeNumber'
    ];

    protected $with = ['authors'];

    public function authors()
    {
        return $this->belongsToMany(Person::class, 'person_work')->withTimestamps();
    }
}
