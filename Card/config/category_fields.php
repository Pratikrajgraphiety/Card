<?php

return [
    'business' => [
        ['name' => 'company_name', 'label' => 'Company Name', 'type' => 'text', 'section' => 'Business'],
        ['name' => 'services_summary', 'label' => 'Services', 'type' => 'textarea', 'section' => 'Business'],
        ['name' => 'products_summary', 'label' => 'Products', 'type' => 'textarea', 'section' => 'Business'],
        ['name' => 'business_pdf_path', 'label' => 'Business PDF / Catalog', 'type' => 'file', 'accept' => '.pdf', 'section' => 'Business'],
        ['name' => 'whatsapp', 'label' => 'WhatsApp Number', 'type' => 'tel', 'section' => 'Business'],
        ['name' => 'maps_url', 'label' => 'Google Maps URL', 'type' => 'url', 'section' => 'Business'],
        ['name' => 'business_hours', 'label' => 'Business Hours', 'type' => 'textarea', 'section' => 'Business'],
    ],
    'student' => [
        ['name' => 'college', 'label' => 'College', 'type' => 'text', 'section' => 'Student'],
        ['name' => 'course', 'label' => 'Course', 'type' => 'text', 'section' => 'Student'],
        ['name' => 'skills_summary', 'label' => 'Skills', 'type' => 'textarea', 'section' => 'Student'],
        ['name' => 'projects_summary', 'label' => 'Projects', 'type' => 'textarea', 'section' => 'Student'],
        ['name' => 'certificates', 'label' => 'Certificates', 'type' => 'textarea', 'section' => 'Student'],
        ['name' => 'resume', 'label' => 'Resume Upload', 'type' => 'file', 'accept' => '.pdf,.doc,.docx', 'section' => 'Student'],
        ['name' => 'github', 'label' => 'GitHub', 'type' => 'url', 'section' => 'Student'],
        ['name' => 'linkedin', 'label' => 'LinkedIn', 'type' => 'url', 'section' => 'Student'],
    ],
    'professional' => [
        ['name' => 'profession', 'label' => 'Profession', 'type' => 'text', 'section' => 'Professional'],
        ['name' => 'experience', 'label' => 'Experience', 'type' => 'textarea', 'section' => 'Professional'],
        ['name' => 'qualifications', 'label' => 'Qualifications', 'type' => 'textarea', 'section' => 'Professional'],
        ['name' => 'appointment_link', 'label' => 'Appointment Link', 'type' => 'url', 'section' => 'Professional'],
    ],
    'creator' => [
        ['name' => 'instagram', 'label' => 'Instagram', 'type' => 'url', 'section' => 'Creator'],
        ['name' => 'youtube', 'label' => 'YouTube', 'type' => 'url', 'section' => 'Creator'],
        ['name' => 'featured_videos', 'label' => 'Featured Videos', 'type' => 'textarea', 'section' => 'Creator'],
        ['name' => 'gallery_note', 'label' => 'Gallery Note', 'type' => 'textarea', 'section' => 'Creator'],
        ['name' => 'donation_link', 'label' => 'Donation Link', 'type' => 'url', 'section' => 'Creator'],
    ],
    'freelancer' => [
        ['name' => 'skills_summary', 'label' => 'Skills', 'type' => 'textarea', 'section' => 'Freelancer'],
        ['name' => 'services_summary', 'label' => 'Services', 'type' => 'textarea', 'section' => 'Freelancer'],
        ['name' => 'portfolio_summary', 'label' => 'Portfolio', 'type' => 'textarea', 'section' => 'Freelancer'],
        ['name' => 'testimonials_summary', 'label' => 'Testimonials', 'type' => 'textarea', 'section' => 'Freelancer'],
        ['name' => 'pricing_summary', 'label' => 'Pricing', 'type' => 'textarea', 'section' => 'Freelancer'],
    ],
    'job-seeker' => [
        ['name' => 'resume', 'label' => 'Resume Upload', 'type' => 'file', 'accept' => '.pdf,.doc,.docx', 'section' => 'Job Seeker'],
        ['name' => 'education_summary', 'label' => 'Education', 'type' => 'textarea', 'section' => 'Job Seeker'],
        ['name' => 'experience', 'label' => 'Experience', 'type' => 'textarea', 'section' => 'Job Seeker'],
        ['name' => 'skills_summary', 'label' => 'Skills', 'type' => 'textarea', 'section' => 'Job Seeker'],
        ['name' => 'portfolio_summary', 'label' => 'Portfolio', 'type' => 'textarea', 'section' => 'Job Seeker'],
    ],
];
