<x-layouts.app title="Nice Smile Dental - New Appointment" page-title="New Appointment" :show-branch-filter="true">
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Form Container -->
        <x-form.container title="Schedule New Appointment"
            subtitle="Fill in the details to create a new patient appointment"
            action="#" method="POST">
            <!-- Patient Information Section -->
            <div class="space-y-6">
                <div class="border-l-4 border-blue-500 pl-4">
                    <h4 class="text-lg font-semibold text-slate-800 dark:text-slate-200 mb-4">Patient Information</h4>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <x-form.field label="First Name" name="first_name" value="{{ old('first_name') }}" required
                        icon="fas fa-user" placeholder="Enter first name" />

                    <x-form.field label="Last Name" name="last_name" value="{{ old('last_name') }}" required
                        icon="fas fa-user" placeholder="Enter last name" />

                    <x-form.field label="Email Address" name="email" type="email" value="{{ old('email') }}" required
                        icon="fas fa-envelope" placeholder="patient@example.com" />

                    <x-form.field label="Phone Number" name="phone" value="{{ old('phone') }}" required prefix="+63"
                        placeholder="9XX XXX XXXX" />

                    <x-form.field label="Date of Birth" name="date_of_birth" type="date"
                        value="{{ old('date_of_birth') }}" required icon="fas fa-calendar" />

                    <x-form.field label="Gender" name="gender" type="select" value="{{ old('gender') }}" required
                        :options="[
                            '' => 'Select Gender',
                            'male' => 'Male',
                            'female' => 'Female',
                            'other' => 'Other'
                        ]" />
                </div>

                <x-form.field label="Address" name="address" type="textarea" value="{{ old('address') }}" rows="3"
                    icon="fas fa-map-marker-alt" placeholder="Complete address"
                    help="Include street, barangay, city, and province" />
            </div>

            <!-- Appointment Details Section -->
            <div class="space-y-6">
                <div class="border-l-4 border-green-500 pl-4">
                    <h4 class="text-lg font-semibold text-slate-800 dark:text-slate-200 mb-4">Appointment Details</h4>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <x-form.field label="Branch" name="branch_id" type="select" value="{{ old('branch_id') }}" required
                        :options="[
                            '' => 'Select Branch',
                            '1' => 'Downtown Branch',
                            '2' => 'Westside Branch',
                            '3' => 'Eastside Branch'
                        ]" />

                    <x-form.field label="Service Type" name="service_type" type="select"
                        value="{{ old('service_type') }}" required :options="[
                            '' => 'Select Service',
                            'consultation' => 'General Consultation',
                            'cleaning' => 'Teeth Cleaning',
                            'filling' => 'Tooth Filling',
                            'extraction' => 'Tooth Extraction',
                            'whitening' => 'Teeth Whitening',
                            'braces' => 'Braces Consultation',
                            'root_canal' => 'Root Canal Treatment',
                            'implant' => 'Dental Implant'
                        ]" />

                    <x-form.field label="Dentist" name="dentist_id" type="select" value="{{ old('dentist_id') }}"
                        required :options="[
                            '' => 'Select Dentist',
                            '1' => 'Dr. Maria Santos',
                            '2' => 'Dr. Juan Cruz',
                            '3' => 'Dr. Ana Reyes',
                            '4' => 'Dr. Carlos Lopez'
                        ]" />

                    <x-form.field label="Appointment Date" name="appointment_date" type="date"
                        value="{{ old('appointment_date') }}" required icon="fas fa-calendar" />

                    <x-form.field label="Appointment Time" name="appointment_time" type="time"
                        value="{{ old('appointment_time') }}" required icon="fas fa-clock" />

                    <x-form.field label="Duration (minutes)" name="duration" type="number"
                        value="{{ old('duration', '60') }}" required min="30" max="240" step="15" suffix="min" />
                </div>

                <x-form.field label="Chief Complaint / Reason for Visit" name="chief_complaint" type="textarea"
                    value="{{ old('chief_complaint') }}" required rows="4"
                    placeholder="Describe the main reason for this appointment"
                    help="Please provide detailed information about symptoms or concerns" />

                <x-form.field label="Additional Notes" name="notes" type="textarea" value="{{ old('notes') }}" rows="3"
                    placeholder="Any additional information or special requests" />
            </div>

            <!-- Medical History Section -->
            <div class="space-y-6">
                <div class="border-l-4 border-orange-500 pl-4">
                    <h4 class="text-lg font-semibold text-slate-800 dark:text-slate-200 mb-4">Medical History</h4>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-form.field label="Allergies" name="allergies" type="textarea" value="{{ old('allergies') }}"
                        rows="3" placeholder="List any known allergies (medications, materials, etc.)" />

                    <x-form.field label="Current Medications" name="medications" type="textarea"
                        value="{{ old('medications') }}" rows="3" placeholder="List current medications and dosages" />
                </div>

                <div class="space-y-4">
                    <p class="text-sm font-medium text-slate-700 dark:text-slate-300">Please check any conditions that
                        apply:</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <x-form.checkbox label="Diabetes" name="medical_conditions[]" value="diabetes"
                            :checked="in_array('diabetes', old('medical_conditions', []))" />

                        <x-form.checkbox label="Hypertension" name="medical_conditions[]" value="hypertension"
                            :checked="in_array('hypertension', old('medical_conditions', []))" />

                        <x-form.checkbox label="Heart Disease" name="medical_conditions[]" value="heart_disease"
                            :checked="in_array('heart_disease', old('medical_conditions', []))" />

                        <x-form.checkbox label="Bleeding Disorders" name="medical_conditions[]"
                            value="bleeding_disorders"
                            :checked="in_array('bleeding_disorders', old('medical_conditions', []))" />

                        <x-form.checkbox label="Pregnant" name="medical_conditions[]" value="pregnant"
                            :checked="in_array('pregnant', old('medical_conditions', []))" />

                        <x-form.checkbox label="Smoking" name="medical_conditions[]" value="smoking"
                            :checked="in_array('smoking', old('medical_conditions', []))" />
                    </div>
                </div>
            </div>

            <!-- Emergency Contact Section -->
            <div class="space-y-6">
                <div class="border-l-4 border-red-500 pl-4">
                    <h4 class="text-lg font-semibold text-slate-800 dark:text-slate-200 mb-4">Emergency Contact</h4>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <x-form.field label="Contact Name" name="emergency_contact_name"
                        value="{{ old('emergency_contact_name') }}" icon="fas fa-user" placeholder="Full name" />

                    <x-form.field label="Relationship" name="emergency_contact_relationship"
                        value="{{ old('emergency_contact_relationship') }}"
                        placeholder="e.g. Spouse, Parent, Sibling" />

                    <x-form.field label="Phone Number" name="emergency_contact_phone"
                        value="{{ old('emergency_contact_phone') }}" prefix="+63" placeholder="9XX XXX XXXX" />
                </div>
            </div>

            <!-- Preferences Section -->
            <div class="space-y-6">
                <div class="border-l-4 border-purple-500 pl-4">
                    <h4 class="text-lg font-semibold text-slate-800 dark:text-slate-200 mb-4">Communication Preferences
                    </h4>
                </div>

                <x-form.radio-group label="Preferred Contact Method" name="preferred_contact"
                    value="{{ old('preferred_contact') }}" :options="[
                        'email' => 'Email',
                        'phone' => 'Phone Call',
                        'sms' => 'SMS/Text Message'
                    ]" inline />

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-form.checkbox label="Send appointment reminders" name="reminders" value="1"
                        :checked="old('reminders', true)" help="Receive notifications 24 hours before appointment" />

                    <x-form.checkbox label="Subscribe to health tips newsletter" name="newsletter" value="1"
                        :checked="old('newsletter')" help="Monthly dental health tips and clinic updates" />
                </div>
            </div>

            <!-- Terms and Agreement Section -->
            <div class="space-y-4">
                <div class="bg-slate-50 dark:bg-slate-800 p-4 rounded-lg border border-slate-200 dark:border-slate-600">
                    <h5 class="font-semibold text-slate-800 dark:text-slate-200 mb-2">Terms and Agreement</h5>
                    <div class="text-sm text-slate-600 dark:text-slate-400 space-y-2">
                        <p>By scheduling this appointment, you agree to the following:</p>
                        <ul class="list-disc list-inside space-y-1 ml-4">
                            <li>Arrive 15 minutes before your scheduled appointment time</li>
                            <li>Provide 24-hour notice for cancellations or rescheduling</li>
                            <li>Bring valid ID and insurance information if applicable</li>
                            <li>Payment is due at the time of service unless prior arrangements are made</li>
                        </ul>
                    </div>
                </div>

                <x-form.checkbox label="I agree to the terms and conditions" name="terms_agreed" value="1" required
                    :checked="old('terms_agreed')" />

                <x-form.checkbox label="I consent to receive appointment reminders and clinic communications"
                    name="communication_consent" value="1" :checked="old('communication_consent', true)" />
            </div>

            <!-- Form Actions -->
            <div
                class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-6 border-t border-slate-200 dark:border-slate-600">
                <div class="flex items-center text-sm text-slate-600 dark:text-slate-400">
                    <i class="fas fa-info-circle mr-2"></i>
                    All fields marked with <span class="text-red-500">*</span> are required
                </div>

                <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4">
                    <x-form.button variant="outline" type="button" href="#"
                        icon="fas fa-arrow-left">
                        Cancel
                    </x-form.button>

                    <x-form.button variant="secondary" type="button" onclick="window.print()" icon="fas fa-save">
                        Save as Draft
                    </x-form.button>

                    <x-form.button type="submit" icon="fas fa-calendar-plus" variant="primary">
                        Schedule Appointment
                    </x-form.button>
                </div>
            </div>
        </x-form.container>

        <!-- Information Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Office Hours Card -->
            <div class="card p-6">
                <div class="flex items-center mb-4">
                    <i class="fas fa-clock text-blue-500 text-xl mr-3"></i>
                    <h4 class="text-lg font-semibold text-slate-800 dark:text-slate-200">Office Hours</h4>
                </div>
                <div class="space-y-2 text-sm text-slate-600 dark:text-slate-400">
                    <div class="flex justify-between">
                        <span>Monday - Friday:</span>
                        <span>8:00 AM - 6:00 PM</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Saturday:</span>
                        <span>8:00 AM - 4:00 PM</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Sunday:</span>
                        <span>Closed</span>
                    </div>
                </div>
            </div>

            <!-- Contact Information Card -->
            <div class="card p-6">
                <div class="flex items-center mb-4">
                    <i class="fas fa-phone text-green-500 text-xl mr-3"></i>
                    <h4 class="text-lg font-semibold text-slate-800 dark:text-slate-200">Contact Info</h4>
                </div>
                <div class="space-y-2 text-sm text-slate-600 dark:text-slate-400">
                    <div class="flex items-center">
                        <i class="fas fa-phone w-4 mr-2"></i>
                        <span>(063) 123-4567</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-envelope w-4 mr-2"></i>
                        <span>info@nicesmile.com</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-map-marker-alt w-4 mr-2"></i>
                        <span>Downtown Tacloban</span>
                    </div>
                </div>
            </div>

            <!-- Services Offered Card -->
            <div class="card p-6">
                <div class="flex items-center mb-4">
                    <i class="fas fa-tooth text-purple-500 text-xl mr-3"></i>
                    <h4 class="text-lg font-semibold text-slate-800 dark:text-slate-200">Our Services</h4>
                </div>
                <div class="space-y-1 text-sm text-slate-600 dark:text-slate-400">
                    <div>• General Consultation</div>
                    <div>• Teeth Cleaning</div>
                    <div>• Tooth Filling</div>
                    <div>• Tooth Extraction</div>
                    <div>• Teeth Whitening</div>
                    <div>• Braces</div>
                    <div>• Root Canal</div>
                    <div>• Dental Implants</div>
                </div>
            </div>
        </div>

        <!-- Quick Actions Card -->
        <div class="card p-6">
            <h4 class="text-lg font-semibold text-slate-800 dark:text-slate-200 mb-4">Quick Actions</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <x-form.button href="#" variant="outline" size="sm" icon="fas fa-list"
                    class="w-full">
                    View All Appointments
                </x-form.button>

                <x-form.button href="#" variant="outline" size="sm" icon="fas fa-users"
                    class="w-full">
                    Patient Records
                </x-form.button>

                <x-form.button href="#" variant="outline" size="sm" icon="fas fa-calendar"
                    class="w-full">
                    Calendar View
                </x-form.button>

                <x-form.button href="#" variant="outline" size="sm" icon="fas fa-chart-bar"
                    class="w-full">
                    Reports
                </x-form.button>
            </div>
        </div>
    </div>
</x-layouts.app>