<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{
    /**
     * Show the form for creating a new company.
     */
    public function create()
    {
        // Redirect if user already has a company
        if (auth()->user()->hasCompany()) {
            return redirect()->route('company.edit')
                ->with('info', 'You already have a company profile. You can edit it here.');
        }

        return view('company.create');
    }

    /**
     * Store a newly created company in storage.
     */
    public function store(Request $request)
    {
        // Check if user already has a company
        if (auth()->user()->hasCompany()) {
            return redirect()->route('dashboard')
                ->with('error', 'You already have a company profile.');
        }

        $validated = $this->validateCompany($request);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('company-logos', 'public');
            $validated['logo'] = $logoPath;
        }

        // Create company for authenticated user
        $validated['user_id'] = auth()->id();
        
        $company = Company::create($validated);

        return redirect()->route('dashboard')
            ->with('success', 'Company profile created successfully! You can now start creating invoices.');
    }

    /**
     * Show the form for editing the company.
     */
    public function edit()
    {
        $company = auth()->user()->company;

        if (!$company) {
            return redirect()->route('company.create')
                ->with('info', 'Please set up your company profile first.');
        }

        return view('company.edit', compact('company'));
    }

    /**
     * Update the company in storage.
     */
    public function update(Request $request)
    {
        $company = auth()->user()->company;

        if (!$company) {
            return redirect()->route('company.create')
                ->with('error', 'No company profile found. Please create one first.');
        }

        $validated = $this->validateCompany($request, $company->id);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($company->logo && Storage::disk('public')->exists($company->logo)) {
                Storage::disk('public')->delete($company->logo);
            }
            
            $logoPath = $request->file('logo')->store('company-logos', 'public');
            $validated['logo'] = $logoPath;
        }

        $company->update($validated);

        return redirect()->route('company.edit')
            ->with('success', 'Company profile updated successfully!');
    }

    /**
     * Validate company data.
     */
    private function validateCompany(Request $request, $companyId = null): array
    {
        $rules = [
            // Basic Information
            'company_name' => ['required', 'string', 'max:255'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'company_phone' => ['nullable', 'string', 'max:20'],
            'website' => ['nullable', 'url', 'max:255'],
            
            // Address Information
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:100'],
            
            // Business Details
            'tax_id' => ['nullable', 'string', 'max:50'],
            'business_type' => ['nullable', 'string', 'max:100'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            
            // Invoice Settings
            'invoice_prefix' => ['required', 'string', 'max:10'],
            'next_invoice_number' => ['required', 'integer', 'min:1'],
            'default_payment_terms' => ['required', 'string'],
            'currency' => ['required', 'string', 'size:3'],
        ];

        // Add unique validation for invoice prefix if updating
        if ($companyId) {
            $rules['invoice_prefix'][] = Rule::unique('companies')->ignore($companyId);
        }

        return $request->validate($rules, [
            'company_name.required' => 'Company name is required.',
            'address_line_1.required' => 'Address is required.',
            'city.required' => 'City is required.',
            'state.required' => 'State is required.',
            'postal_code.required' => 'Postal code is required.',
            'country.required' => 'Country is required.',
            'invoice_prefix.required' => 'Invoice prefix is required.',
            'invoice_prefix.unique' => 'This invoice prefix is already taken.',
            'next_invoice_number.required' => 'Starting invoice number is required.',
            'next_invoice_number.min' => 'Invoice number must be at least 1.',
            'default_payment_terms.required' => 'Payment terms are required.',
            'currency.required' => 'Currency is required.',
            'logo.image' => 'Logo must be an image file.',
            'logo.mimes' => 'Logo must be a JPEG, PNG, JPG, or GIF file.',
            'logo.max' => 'Logo file size cannot exceed 2MB.',
        ]);
    }
}