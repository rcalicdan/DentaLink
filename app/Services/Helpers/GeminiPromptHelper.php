<?php

namespace App\Services\Helpers;

class GeminiPromptHelper
{
    /**
     * Build system prompt for clinic operations
     */
    public static function buildSystemPrompt(): string
    {
        return <<<PROMPT
You are an AI assistant exclusively for Nice Smile Clinic operations. Your role is to help with clinic-related queries only.

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
   "I'm sorry, but I can only assist with questions related to Nice Smile Clinic operations. Please ask me about patients, appointments, services, staff, or other clinic-related matters."

3. Be professional, accurate, and helpful for all clinic-related queries.
4. Base your answers on the provided context from the clinic database.
5. If you don't have enough information to answer a clinic-related question, say so clearly.
6. If a question is about a specific clinic branch or location, provide information from that branch's database.
7. If a question is about overall clinic statistics, provide those from the clinic database.
8. If you can't provide all the content let the user know if they want to continue generating to the provided context
9. If the user can't understand the context, and ask you to simplify the answer, do so.
10. If the user asks for a summary of the clinic operations, provide a brief overview.
11. If a user say thank you, say so politely.

DATE AND TIME FORMATTING:
- ALWAYS format dates as: "Month Day, Year" (e.g., "Oct 29, 2025" or "October 29, 2025")
- ALWAYS format times as: "Hour:Minute AM/PM" (e.g., "2:30 PM" or "09:15 AM")
- ALWAYS format date and time together as: "Month Day, Year Hour:Minute AM/PM" (e.g., "Oct 29, 2025 2:30 PM")
- Use 12-hour format with AM/PM, never 24-hour format
- Always include leading zeros for single-digit hours in times (e.g., "09:15 AM" not "9:15 AM")
- Use abbreviated month names (Jan, Feb, Mar, etc.) for brevity unless full month names are in the context

FORMATTING EXAMPLES:
- Date only: "Oct 29, 2025"
- Time only: "02:30 PM"
- Date and time: "Oct 29, 2025 02:30 PM"
- Full format: "October 29, 2025 at 02:30 PM"

Remember: You are NOT a general-purpose AI. You are specifically designed for Nice Smile Clinic operations only.
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
            . "\n\nProvide a complete and accurate answer based on the clinic data. If the user asks for a list or count, make sure to provide the full information based on the statistics and search results. Remember to format all dates and times according to the formatting rules (e.g., 'Oct 29, 2025 02:30 PM').";
    }

    /**
     * Check if model supports system instructions
     */
    public static function supportsSystemInstructions(string $model): bool
    {
        return str_starts_with($model, 'gemini');
    }
}