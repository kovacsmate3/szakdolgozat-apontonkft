<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\LocationRequest;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LocationRequestTest extends TestCase
{
    #[Test]
    public function it_validates_required_fields_for_creation()
    {
        // Create a mock request for POST (creation)
        $request = new LocationRequest();
        $request->setMethod('POST');

        // Get validation rules
        $rules = $request->rules();

        // Assert required fields have the 'required' rule
        $this->assertContains('required', $rules['name']);
        $this->assertContains('required', $rules['location_type']);
    }

    #[Test]
    public function it_validates_fields_for_update()
    {
        // Create a mock request for PUT (update)
        $request = new LocationRequest();
        $request->setMethod('PUT');

        // Get validation rules
        $rules = $request->rules();

        // Assert fields have the 'sometimes' rule for updates
        $this->assertStringContainsString('sometimes', $rules['name'][0]);
        $this->assertStringContainsString('sometimes', $rules['location_type'][0]);
        $this->assertStringContainsString('sometimes', $rules['is_headquarter'][0]);
    }

    #[Test]
    public function it_validates_field_types_correctly()
    {
        // Create a mock request
        $request = new LocationRequest();

        // Get validation rules
        $rules = $request->rules();

        // Assert field type validation rules
        $this->assertContains('string', $rules['name']);
        $this->assertContains('string', $rules['location_type']);
        $this->assertContains('boolean', $rules['is_headquarter']);
    }

    #[Test]
    public function it_validates_maximum_field_lengths()
    {
        // Create a mock request
        $request = new LocationRequest();

        // Get validation rules
        $rules = $request->rules();

        // Assert maximum length validation rules
        $this->assertContains('max:255', $rules['name']);
    }

    #[Test]
    public function it_validates_location_types()
    {
        // Create a mock request
        $request = new LocationRequest();

        // Get validation rules
        $rules = $request->rules();

        // Assert location type validation rule
        $this->assertContains('in:partner,telephely,töltőállomás,bolt,egyéb', $rules['location_type']);
    }

    #[Test]
    public function it_provides_custom_error_messages()
    {
        // Create a mock request
        $request = new LocationRequest();

        // Get error messages
        $messages = $request->messages();

        // Assert important error messages exist
        $this->assertArrayHasKey('name.required', $messages);
        $this->assertArrayHasKey('location_type.required', $messages);
        $this->assertArrayHasKey('location_type.in', $messages);
        $this->assertArrayHasKey('is_headquarter.boolean', $messages);
    }

    #[Test]
    public function it_rejects_invalid_data()
    {
        // Create a validator with invalid data
        $validator = Validator::make(
            [
                'name' => '', // Empty string
                'location_type' => 'invalid_type', // Not in the allowed list
                'is_headquarter' => 'not-a-boolean' // Not a boolean
            ],
            (new LocationRequest())->rules(),
            (new LocationRequest())->messages()
        );

        // Assert validation fails
        $this->assertTrue($validator->fails());

        // Assert specific validation errors
        $errors = $validator->errors();
        $this->assertTrue($errors->has('name'));
        $this->assertTrue($errors->has('location_type'));
        $this->assertTrue($errors->has('is_headquarter'));
    }

    #[Test]
    public function it_accepts_valid_data()
    {
        // Create a validator with valid data
        $validator = Validator::make(
            [
                'name' => 'Test Location',
                'location_type' => 'partner',
                'is_headquarter' => false
            ],
            (new LocationRequest())->rules(),
            (new LocationRequest())->messages()
        );

        // Assert validation passes
        $this->assertFalse($validator->fails());
    }
}
