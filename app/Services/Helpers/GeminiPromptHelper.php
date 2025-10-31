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
12. **Date and Time Formatting:**
   - Always present dates in conversational, human-readable format
   - Use natural language like "January 15, 2024" instead of "2024-01-15"
   - For recent dates, use relative terms: "today", "yesterday", "tomorrow", "last week", "next Monday"
   - Include time in 12-hour format with AM/PM: "2:30 PM" instead of "14:30"
   - When discussing date ranges, make them natural: "from January 1 to January 31, 2024"
   - For timestamps, use phrases like "on January 15, 2024 at 2:30 PM"

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
        $context = "# Knowledge Base Context\n\n";

        // Add statistics
        if (!empty($stats)) {
            $context .= "## Available Data:\n";
            foreach ($stats as $entityType => $count) {
                $readableName = self::getReadableEntityName($entityType);
                $context .= "- {$readableName}: " . number_format($count) . " records\n";
            }
            $context .= "\n";
        }

        // Add search results
        if (!empty($searchResults)) {
            $context .= "## Relevant Information:\n\n";
            foreach ($searchResults as $index => $result) {
                $context .= "### Result " . ($index + 1) . " (Relevance: " . number_format($result['similarity'] * 100, 1) . "%)\n";
                $context .= $result['content'] . "\n\n";
            }
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
     * Build user prompt with enhanced context
     */
    public static function buildEnhancedUserPrompt(string $context, string $userMessage, bool $isFirstMessage = false): string
    {
        if ($isFirstMessage) {
            return <<<PROMPT
{$context}

---

This is the user's first message. They are greeting you or starting a new conversation.

User's message: {$userMessage}

Please provide a warm, helpful introduction and ask how you can assist them with clinic information today.
PROMPT;
        }

        return <<<PROMPT
{$context}

---

User's question: {$userMessage}

Please provide a helpful, accurate response based on the available data above. Remember to:
- Use natural, conversational language
- Format dates and times in a human-readable way
- Present currency in Philippine Peso (₱) format
- If the information isn't in the context, politely say so
- Be concise but thorough
PROMPT;
    }

    /**
     * Check if model supports system instructions
     */
    public static function supportsSystemInstructions(string $model): bool
    {
        return str_starts_with($model, 'gemini');
    }


    /**
     * Get current date formatted
     */
    private static function getCurrentDate(): string
    {
        return now()->format('l, F d, Y');
    }

    /**
     * Get readable entity name
     */
    private static function getReadableEntityName(string $entityType): string
    {
        return match ($entityType) {
            'user' => 'Users',
            'patient' => 'Patients',
            'appointment' => 'Appointments',
            'dental_service' => 'Dental Services',
            'patient_visit' => 'Patient Visits',
            'branch' => 'Branches',
            'dental_service_type' => 'Service Categories',
            'inventory' => 'Inventory Items',
            'patient_visit_service' => 'Service Records',
            'audit_log' => 'Activity Logs',
            default => ucfirst(str_replace('_', ' ', $entityType))
        };
    }
}
