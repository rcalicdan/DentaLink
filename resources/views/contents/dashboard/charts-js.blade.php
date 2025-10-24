@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        function appointmentsChart() {
            return {
                chartLoaded: false,
                init() {
                    try {
                        const ctx = this.$refs.chart;
                        if (!ctx) return;

                        const chartData = @json($weeklyAppointments);
                        if (!chartData || !chartData.labels || !chartData.data) return;
                        if (chartData.labels.length === 0) return;

                        const isDarkMode = document.documentElement.classList.contains('dark');
                        const gridColor = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
                        const textColor = isDarkMode ? '#cbd5e1' : '#475569';

                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: chartData.labels,
                                datasets: [{
                                    label: 'Appointments',
                                    data: chartData.data,
                                    backgroundColor: 'rgba(59, 130, 246, 0.5)',
                                    borderColor: 'rgba(59, 130, 246, 1)',
                                    borderWidth: 2,
                                    borderRadius: 4,
                                    hoverBackgroundColor: 'rgba(59, 130, 246, 0.7)',
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        grid: {
                                            color: gridColor
                                        },
                                        ticks: {
                                            color: textColor,
                                            precision: 0
                                        }
                                    },
                                    x: {
                                        grid: {
                                            display: false
                                        },
                                        ticks: {
                                            color: textColor
                                        }
                                    }
                                },
                                plugins: {
                                    legend: {
                                        display: false
                                    }
                                }
                            }
                        });
                        this.chartLoaded = true;
                    } catch (error) {}
                }
            }
        }

        function servicesChart() {
            return {
                chartLoaded: false,
                init() {
                    try {
                        const ctx = this.$refs.chart;
                        if (!ctx) return;

                        const chartData = @json($servicesBreakdown);
                        if (!chartData || !chartData.labels || !chartData.data) return;
                        if (chartData.labels.length === 0 || chartData.data.length === 0) return;

                        const isDarkMode = document.documentElement.classList.contains('dark');
                        const textColor = isDarkMode ? '#cbd5e1' : '#475569';

                        new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: chartData.labels,
                                datasets: [{
                                    data: chartData.data,
                                    backgroundColor: [
                                        '#3B82F6', '#10B981', '#8B5CF6',
                                        '#F97316', '#64748B', '#EC4899', '#14B8A6'
                                    ],
                                    hoverOffset: 4
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: {
                                            color: textColor,
                                            padding: 15,
                                            font: {
                                                size: 11
                                            }
                                        }
                                    }
                                }
                            }
                        });
                        this.chartLoaded = true;
                    } catch (error) {}
                }
            }
        }
    </script>
@endpush
