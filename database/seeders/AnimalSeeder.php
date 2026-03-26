<?php

namespace Database\Seeders;

use App\Models\Pet; 
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AnimalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $animals = [
            [
                'name' => 'Buddy',
                'species' => 'dog',
                'age' => 2,
                'size' => 'medium',
                'gender' => 'Male',
                'breed' => 'Golden Retriever',
                'status' => 'Available',
                'image_path' => 'https://placehold.co/400x400/f97316/ffffff?text=Buddy',
                'description' => 'Buddy is a playful and friendly Golden Retriever who loves fetch and long walks. He gets along well with kids and other dogs.',
            ],
            [
                'name' => 'Lucy',
                'species' => 'dog',
                'age' => 5,
                'size' => 'large',
                'gender' => 'Female',
                'breed' => 'German Shepherd',
                'status' => 'Available',
                'image_path' => 'https://placehold.co/400x400/4f46e5/ffffff?text=Lucy',
                'description' => 'Lucy is a loyal and intelligent companion. She is very protective and would do best in a home with an experienced owner.',
            ],
            [
                'name' => 'Whiskers',
                'species' => 'cat',
                'age' => 0.5,
                'size' => 'small',
                'gender' => 'Male',
                'breed' => 'Tabby',
                'status' => 'Available',
                'image_path' => 'https://placehold.co/400x400/10b981/ffffff?text=Whiskers',
                'description' => 'Whiskers is a curious and energetic kitten. He loves chasing laser pointers and cuddling up for naps in sunny spots.',
            ],
            [
                'name' => 'Sadie',
                'species' => 'dog',
                'age' => 10,
                'size' => 'small',
                'gender' => 'Female',
                'breed' => 'Beagle',
                'status' => 'Available',
                'image_path' => 'https://placehold.co/400x400/64748b/ffffff?text=Sadie',
                'description' => 'Sadie is a sweet and gentle senior beagle looking for a quiet home to spend her golden years. She loves soft beds and short, leisurely strolls.',
            ],
            [
                'name' => 'Leo',
                'species' => 'cat',
                'age' => 1,
                'size' => 'medium',
                'gender' => 'Male',
                'breed' => 'Siamese',
                'status' => 'Available',
                'image_path' => 'https://placehold.co/400x400/3b82f6/ffffff?text=Leo',
                'description' => 'Leo is a vocal and affectionate Siamese cat. He enjoys being the center of attention and will follow you around the house.',
            ],
            [
                'name' => 'Max',
                'species' => 'dog',
                'age' => 4,
                'size' => 'medium',
                'gender' => 'Male',
                'breed' => 'Labrador Mix',
                'status' => 'Available',
                'image_path' => 'https://placehold.co/400x400/ef4444/ffffff?text=Max',
                'description' => 'Max is a happy-go-lucky lab mix with a heart of gold. He is great with everyone he meets and has a lot of love to give.',
            ],
            [
                'name' => 'Chloe',
                'species' => 'cat',
                'age' => 3,
                'size' => 'small',
                'gender' => 'Female',
                'breed' => 'Persian',
                'status' => 'Available',
                'image_path' => 'https://placehold.co/400x400/8b5cf6/ffffff?text=Chloe',
                'description' => 'Chloe is a calm and dignified Persian cat. She enjoys being groomed and prefers a peaceful environment.',
            ],
            [
                'name' => 'Rocky',
                'species' => 'dog',
                'age' => 1,
                'size' => 'large',
                'gender' => 'Male',
                'breed' => 'Boxer',
                'status' => 'Available',
                'image_path' => 'https://placehold.co/400x400/f59e0b/ffffff?text=Rocky',
                'description' => 'Rocky is a bundle of energy and fun! This young Boxer loves to play and would be a great fit for an active family.',
            ],
        ];

        foreach ($animals as $animal) {
            Pet::create($animal); 
        }
    }
}