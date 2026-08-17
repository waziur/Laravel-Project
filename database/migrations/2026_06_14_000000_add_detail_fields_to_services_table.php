<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->text('detail_overview')->nullable();
            $table->json('included_services')->nullable();
            $table->json('delivery_steps')->nullable();
        });

        $now = now();

        foreach ($this->defaultDetails() as $title => $details) {
            DB::table('services')
                ->where('title', $title)
                ->update([
                    'detail_overview' => $details['detail_overview'],
                    'included_services' => json_encode($details['included_services']),
                    'delivery_steps' => json_encode($details['delivery_steps']),
                    'updated_at' => $now,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['detail_overview', 'included_services', 'delivery_steps']);
        });
    }

    /**
     * @return array<string, array{detail_overview: string, included_services: array<int, string>, delivery_steps: array<int, string>}>
     */
    private function defaultDetails(): array
    {
        return [
            'Cyber Security' => [
                'detail_overview' => 'Protect your website, application, and business data with practical security reviews, hardening, monitoring, and incident response planning.',
                'included_services' => [
                    'Security audit and vulnerability assessment',
                    'Firewall, access control, and authentication hardening',
                    'Monitoring setup with alert response guidance',
                    'Incident response checklist and recovery documentation',
                ],
                'delivery_steps' => [
                    'Review the current system, user access, and public attack surface.',
                    'Prioritize risks by business impact and fix effort.',
                    'Apply security improvements and verify the vulnerable areas again.',
                    'Deliver a clear report with maintenance guidance for the team.',
                ],
            ],
            'Data Analytics' => [
                'detail_overview' => 'Turn raw business data into useful dashboards, reports, and decision workflows that help teams understand performance faster.',
                'included_services' => [
                    'Data source review and reporting requirement planning',
                    'Dashboard design for KPIs, sales, users, and operations',
                    'Data cleaning, transformation, and visualization setup',
                    'Recurring report workflow and stakeholder handover',
                ],
                'delivery_steps' => [
                    'Identify the questions your business needs the data to answer.',
                    'Prepare clean datasets and select the right dashboard structure.',
                    'Build visual reports with filters, metrics, and export options.',
                    'Train the team to read, update, and act on the reports.',
                ],
            ],
            'Web Development' => [
                'detail_overview' => 'Build responsive websites and Laravel applications with clean user flows, maintainable code, admin tools, and performance-focused delivery.',
                'included_services' => [
                    'Business website and landing page development',
                    'Laravel application modules, dashboards, and admin panels',
                    'Responsive UI implementation for mobile and desktop',
                    'Performance, validation, and deployment support',
                ],
                'delivery_steps' => [
                    'Collect requirements, content needs, and core user journeys.',
                    'Design the page structure, database flow, and key interactions.',
                    'Develop, test, and refine the website or application modules.',
                    'Launch the project and provide update guidance for admins.',
                ],
            ],
            'App Development' => [
                'detail_overview' => 'Plan and build customer-facing or internal applications that make daily workflows easier, faster, and more reliable.',
                'included_services' => [
                    'App feature planning and user flow mapping',
                    'Cross-platform app interface and backend integration',
                    'Authentication, forms, notifications, and data storage',
                    'Testing, release preparation, and maintenance planning',
                ],
                'delivery_steps' => [
                    'Define the target users, app goals, and must-have features.',
                    'Create the app structure and connect it with the backend data flow.',
                    'Test real workflows across common devices and screen sizes.',
                    'Prepare release assets and support the first production rollout.',
                ],
            ],
            'SEO Optimization' => [
                'detail_overview' => 'Improve discoverability with technical SEO, page speed, content structure, and metadata updates that help search engines understand your website.',
                'included_services' => [
                    'Technical SEO audit for pages, metadata, and crawl issues',
                    'Keyword mapping and content structure recommendations',
                    'Page speed, image, and internal linking improvements',
                    'Search performance tracking and reporting setup',
                ],
                'delivery_steps' => [
                    'Audit the current website structure, speed, and indexing status.',
                    'Map target keywords to the pages that should rank for them.',
                    'Apply metadata, content, performance, and linking improvements.',
                    'Track search changes and recommend the next improvement cycle.',
                ],
            ],
        ];
    }
};
