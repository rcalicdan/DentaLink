<?php

namespace App\Services\Helpers;

use Carbon\Carbon;

class GeminiPromptHelper
{
    public static function buildSystemPrompt(): string
    {
        $now = Carbon::now();
        $currentDate = $now->format('F j, Y');
        $currentTime = $now->format('g:i A');
        $currentDay = $now->format('l');

        return <<<PROMPT
You are an AI assistant exclusively for Nice Smile Clinic operations. Your role is to help with clinic-related queries only.

CURRENT DATE AND TIME INFORMATION:
- Today is: {$currentDay}, {$currentDate}
- Current time: {$currentTime}
- Use this information to interpret relative terms like "today", "tomorrow", "yesterday", "this week", "this month", etc.

CRITICAL DATE AND TIME FORMATTING RULES - YOU MUST FOLLOW THESE EXACTLY:

When displaying dates, you MUST use this EXACT format:
- CORRECT: "November 1, 2025" or "October 29, 2025"
- WRONG: "November 1, 225" or "Nov 1, 225"
- Always include the full 4-digit year (2025, not 225)

When displaying times, you MUST use this EXACT format:
- CORRECT: "8:02 AM - 11:02 AM" or "1:00 PM - 4:00 PM"
- WRONG: "8:2 AM - 1:2 AM" or "1: PM - 4: PM"
- Always include two digits for minutes with leading zero (02, not 2)
- Always include two digits for hours when showing minutes (08:02, not 8:2)

FORMATTING EXAMPLES YOU MUST FOLLOW:
✓ CORRECT DATE FORMATS:
  - "November 1, 2025"
  - "October 28, 2025"
  - "December 31, 2024"

✓ CORRECT TIME FORMATS:
  - "8:00 AM"
  - "8:02 AM"
  - "11:30 PM"
  - "8:02 AM - 11:02 AM"
  - "1:00 PM - 4:00 PM"

✗ INCORRECT FORMATS TO AVOID:
  - "November 1, 225" (missing digit in year)
  - "8:2 AM" (missing leading zero in minutes)
  - "1: PM" (missing minutes entirely)

WHEN YOU SEE THESE PATTERNS IN THE DATA, COPY THEM EXACTLY:
- If you see "November 1, 2025" → Write "November 1, 2025"
- If you see "8:02 AM - 11:02 AM" → Write "8:02 AM - 11:02 AM"
- If you see "1:00 PM - 4:00 PM" → Write "1:00 PM - 4:00 PM"

DO NOT abbreviate, truncate, or modify dates and times. Copy them character-by-character as they appear in the context.

IMPORTANT RULES:
1. ONLY answer questions related to Nice Smile Clinic operations, including:
   - Patient information and records
   - Appointments and scheduling
   - Dental services and procedures and Types
   - Staff and employee information
   - Patient visits and treatment history
   - Clinic branches and locations
   - Billing and payment information
   - Clinic Inventory Management
   - Clinic Revenue
   - Clinic Audit Logs
   - Overall Clinic Statistics

2. For ANY question NOT related to Nice Smile Clinic operations, politely decline and redirect:
   "I'm sorry, but I can only assist with questions related to Nice Smile Clinic operations."

3. Be professional, accurate, and helpful for all clinic-related queries.
4. Base your answers on the provided context from the clinic database.
5. When listing information, preserve ALL formatting from the context exactly as provided.
6. If you don't have enough information, say so clearly.
7. If a user says thank you, respond politely.

Remember: You are specifically designed for Nice Smile Clinic operations only. When you copy dates and times from the context, copy them EXACTLY as they appear.
PROMPT;
    }

    /**
     * Build context from search results
     */
    public static function buildContext(array $searchResults): string
    {
        if (empty($searchResults)) {
            return '';
        }

        $context = "Here is relevant information from the clinic database:\n\n";
        foreach ($searchResults as $result) {
            $context .= "- " . $result['content'] . "\n";
        }
        $context .= "\n";

        return $context;
    }

    /**
     * Build enhanced context with statistics
     */
    public static function buildEnhancedContext(array $stats, array $searchResults): string
    {
        $context = "Nice Smile Clinic Database Statistics:\n";
        foreach ($stats as $type => $count) {
            $context .= "- Total {$type}s: {$count}\n";
        }
        $context .= "\nRelevant information from the clinic:\n";

        foreach ($searchResults as $index => $result) {
            $relevance = round($result['similarity_score'] * 100, 1);
            $context .= ($index + 1) . ". " . $result['content'] . " (Relevance: {$relevance}%)\n";
        }

        return $context;
    }

    /**
     * Build user prompt
     */
    public static function buildUserPrompt(string $context, string $userMessage, bool $isFirstMessage): string
    {
        $conversationHint = $isFirstMessage
            ? "This is the user's first message in this conversation.\n"
            : "This is a follow-up message in an ongoing conversation.\n";

        return $conversationHint . $context . "User message: " . $userMessage;
    }

    /**
     * Build enhanced user prompt
     */
    public static function buildEnhancedUserPrompt(string $context, string $userMessage, bool $isFirstMessage): string
    {
        $conversationHint = $isFirstMessage
            ? "This is the user's first message in this conversation.\n"
            : "This is a follow-up message in an ongoing conversation.\n";

        return $conversationHint
            . $context
            . "\nUser question: " . $userMessage
            . "\n\nProvide a complete and accurate answer based on the clinic data. If the user asks for a list or count, make sure to provide the full information based on the statistics and search results. CRITICAL: Convert ALL dates and times from database formats to human-readable formats as specified in the system prompt (e.g., 'October 29, 2025 at 2:30 PM', not '2025-10-29 14:30:00').";
    }

    /**
     * Check if model supports system instructions
     */
    public static function supportsSystemInstructions(string $model): bool
    {
        return str_starts_with($model, 'gemini');
    }
}
