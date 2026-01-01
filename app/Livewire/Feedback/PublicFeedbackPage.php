<?php

namespace App\Livewire\Feedback;

use App\Models\Feedback;
use Livewire\Attributes\Layout;
use Livewire\Component;


#[Layout('components.layouts.guest')]
class PublicFeedbackPage extends Component
{
    public $email = '';
    public $content = '';
    public $rating = 0;
    public $hoveredRating = 0;
    public $submitted = false;

    protected $rules = [
        'email' => 'nullable|email|max:255',
        'content' => 'required|string|min:10|max:1000',
        'rating' => 'required|integer|min:1|max:5',
    ];

    protected $messages = [
        'rating.required' => 'Please select a rating',
        'rating.min' => 'Please select at least 1 star',
        'content.min' => 'Please provide at least 10 characters of feedback',
    ];

    public function setRating($rating)
    {
        $this->rating = $rating;
    }

    public function setHoveredRating($rating)
    {
        $this->hoveredRating = $rating;
    }

    public function resetHoveredRating()
    {
        $this->hoveredRating = 0;
    }

    public function submit()
    {
        $this->validate();

        Feedback::create([
            'email' => $this->email,
            'content' => $this->content,
            'rating' => $this->rating,
        ]);

        $this->submitted = true;
        $this->reset(['email', 'content', 'rating', 'hoveredRating']);
    }

    public function submitAnother()
    {
        $this->submitted = false;
    }

    public function render()
    {
        return view('livewire.feedback.public-feedback-page');
    }
}