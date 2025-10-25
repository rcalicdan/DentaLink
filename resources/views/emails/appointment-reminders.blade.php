<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Reminder</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4F46E5;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f9fafb;
            padding: 30px;
            border: 1px solid #e5e7eb;
            border-top: none;
        }
        .appointment-details {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .detail-row {
            display: flex;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: bold;
            color: #6b7280;
            width: 140px;
        }
        .detail-value {
            color: #111827;
        }
        .queue-number {
            background-color: #4F46E5;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
        .button {
            display: inline-block;
            background-color: #4F46E5;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Appointment Reminder</h1>
    </div>
    
    <div class="content">
        <p>Hi {{ $appointment->patient->first_name }},</p>
        
        <p>This is a friendly reminder about your upcoming dental appointment:</p>
        
        <div class="appointment-details">
            <div class="detail-row">
                <div class="detail-label">Patient Name:</div>
                <div class="detail-value">{{ $appointment->patient->full_name }}</div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Date:</div>
                <div class="detail-value">{{ $appointment->appointment_date->format('l, F d, Y') }}</div>
            </div>
            
            @if($appointment->formatted_time_range || $appointment->start_time)
                <div class="detail-row">
                    <div class="detail-label">Time:</div>
                    <div class="detail-value">
                        @if($appointment->formatted_time_range)
                            {{ $appointment->formatted_time_range }}
                        @elseif($appointment->start_time)
                            {{ $appointment->start_time->format('g:i A') }}
                        @endif
                    </div>
                </div>
            @endif
            
            <div class="detail-row">
                <div class="detail-label">Location:</div>
                <div class="detail-value">{{ $appointment->branch->name ?? 'Main Clinic' }}</div>
            </div>
            
            @if($appointment->branch->address)
                <div class="detail-row">
                    <div class="detail-label">Address:</div>
                    <div class="detail-value">{{ $appointment->branch->address }}</div>
                </div>
            @endif
            
            @if($appointment->reason)
                <div class="detail-row">
                    <div class="detail-label">Reason:</div>
                    <div class="detail-value">{{ $appointment->reason }}</div>
                </div>
            @endif
        </div>
        
        @if($appointment->queue_number)
            <div class="queue-number">
                Queue Number: #{{ $appointment->queue_number }}
            </div>
        @endif
        
        <p>Please arrive a few minutes early.</p>
        
        <p>If you need to reschedule or cancel your appointment, please contact us as soon as possible.</p>
        
        <p>We look forward to seeing you!</p>
        
        <div class="footer">
            <p>
                <strong>{{ config('app.name') }}</strong><br>
                @if($appointment->branch->phone ?? false)
                    Phone: {{ $appointment->branch->phone }}<br>
                @endif
                @if($appointment->branch->email ?? false)
                    Email: {{ $appointment->branch->email }}
                @endif
            </p>
        </div>
    </div>
</body>
</html>