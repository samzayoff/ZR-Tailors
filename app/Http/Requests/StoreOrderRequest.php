<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $merge = [];
        
        if (empty($this->order_no)) {
            $merge['order_no'] = \App\Models\Order::nextOrderNo();
        }

        foreach (['booking_date', 'delivery_date'] as $field) {
            $val = $this->input($field);
            if (!empty($val) && preg_match('#^\d{1,2}/\d{1,2}/\d{4}$#', $val)) {
                $parsed = \DateTime::createFromFormat('d/m/Y', $val);
                if ($parsed) {
                    $merge[$field] = $parsed->format('Y-m-d');
                }
            }
        }
        
        if (!empty($merge)) {
            $this->merge($merge);
        }
    }

    public function rules(): array
    {
        return [
            // Customer fields
            'name'          => ['required', 'string', 'max:120'],
            'phone'         => ['nullable', 'string', 'max:20'],
            'reference'     => ['nullable', 'string', 'max:120'],
            // Order fields
            'order_no'      => ['required', 'string', 'max:20', 'unique:orders,order_no'],
            'booking_date'  => ['required', 'date'],
            'delivery_date' => ['required', 'date', 'after_or_equal:booking_date'],
            'quantity'      => ['required', 'integer', 'min:1', 'max:99'],
            'price'         => ['required', 'numeric', 'min:0'],
            'advance_paid'  => ['nullable', 'numeric', 'min:0', 'lte:price'],
            'status'        => ['nullable', 'string', 'in:pending,stitching,ready,delivered,returned,cancelled'],
            'colour_note'   => ['nullable', 'string', 'max:150'],
            'extra_notes'   => ['nullable', 'string'],
            // Measurements
            'kameez'        => ['nullable', 'array'],
            'kameez.*'      => ['nullable', 'string', 'max:20'],
            'waistcoat'     => ['nullable', 'array'],
            'waistcoat.*'   => ['nullable', 'string', 'max:20'],
            // Design options
            'design_options' => ['nullable', 'array'],
            'design_options.*' => ['integer', 'exists:design_options,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter the customer name before saving.',
            'order_no.required' => 'The order number is required.',
            'order_no.unique' => 'This order number already exists.',
            'quantity.required' => 'Please specify the quantity.',
            'quantity.min' => 'Quantity must be at least 1.',
            'price.required' => 'Please enter the price before saving the order.',
            'price.numeric' => 'The price must be a valid number.',
            'price.min' => 'The price cannot be negative.',
            'advance_paid.lte' => 'The advance paid cannot exceed the total price.',
            'delivery_date.after_or_equal' => 'Delivery date cannot be before the booking date.',
            'booking_date.date' => 'Please provide a valid booking date.',
            'delivery_date.date' => 'Please provide a valid delivery date.',
        ];
    }
}
