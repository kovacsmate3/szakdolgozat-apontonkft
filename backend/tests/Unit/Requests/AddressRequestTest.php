<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\AddressRequest;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AddressRequestTest extends TestCase
{
    #[Test]
    public function it_validates_required_fields_for_creation()
    {
        // Create a mock request for POST (creation)
        $request = new AddressRequest();
        $request->setMethod('POST');

        // Get validation rules
        $rules = $request->rules();

        // Assert required fields have the 'required' rule
        $this->assertStringContainsString('required', $rules['country'][0]);
        $this->assertStringContainsString('required', $rules['postalcode'][0]);
        $this->assertStringContainsString('required', $rules['city'][0]);
        $this->assertStringContainsString('required', $rules['road_name'][0]);
        $this->assertStringContainsString('required', $rules['public_space_type'][0]);
        $this->assertStringContainsString('required', $rules['building_number'][0]);
    }

    #[Test]
    public function it_validates_fields_for_update()
    {
        // Create a mock request for PUT (update)
        $request = new AddressRequest();
        $request->setMethod('PUT');

        // Get validation rules
        $rules = $request->rules();

        // Assert fields have the 'sometimes' rule for updates
        $this->assertStringContainsString('sometimes', $rules['country'][0]);
        $this->assertStringContainsString('sometimes', $rules['postalcode'][0]);
        $this->assertStringContainsString('sometimes', $rules['city'][0]);
        $this->assertStringContainsString('sometimes', $rules['road_name'][0]);
        $this->assertStringContainsString('sometimes', $rules['public_space_type'][0]);
        $this->assertStringContainsString('sometimes', $rules['building_number'][0]);
    }

    #[Test]
    public function it_validates_field_types_correctly()
    {
        // Create a mock request
        $request = new AddressRequest();

        // Get validation rules
        $rules = $request->rules();

        // Assert field type validation rules
        $this->assertStringContainsString('string', $rules['country'][1]);
        $this->assertStringContainsString('integer', $rules['postalcode'][1]);
        $this->assertStringContainsString('string', $rules['city'][1]);
        $this->assertStringContainsString('string', $rules['road_name'][1]);
        $this->assertStringContainsString('string', $rules['public_space_type'][1]);
        $this->assertStringContainsString('string', $rules['building_number'][1]);
    }

    #[Test]
    public function it_validates_maximum_field_lengths()
    {
        // Create a mock request
        $request = new AddressRequest();

        // Get validation rules
        $rules = $request->rules();

        // Assert maximum length validation rules
        $this->assertStringContainsString('max:100', $rules['country'][2]);
        $this->assertStringContainsString('max:100', $rules['city'][2]);
        $this->assertStringContainsString('max:100', $rules['road_name'][2]);
        $this->assertStringContainsString('max:50', $rules['public_space_type'][2]);
        $this->assertStringContainsString('max:50', $rules['building_number'][2]);
    }

    #[Test]
    public function it_validates_postalcode_format()
    {
        // Create a mock request
        $request = new AddressRequest();

        // Get validation rules
        $rules = $request->rules();

        // Assert postalcode validation rules
        $this->assertStringContainsString('regex:/^[0-9]{4}$/', $rules['postalcode'][2]);
    }

    #[Test]
    public function it_allows_valid_public_space_types()
    {
        // Create a mock request
        $request = new AddressRequest();

        // Get validation rules
        $rules = $request->rules();

        // Get the list of allowed public space types by extracting from the 'in' rule
        $inRule = collect($rules['public_space_type'])->first(function ($rule) {
            return strpos($rule, 'in:') === 0;
        });

        // Assert the rule exists
        $this->assertNotNull($inRule);

        // Test a few common public space types
        $validTypes = [
            'utca',
            'út',
            'tér',
            'köz',
            'sétány',
            'körút'
        ];

        foreach ($validTypes as $type) {
            // Check if the validation rule contains this type
            $this->assertStringContainsString($type, $inRule);
        }
    }

    #[Test]
    public function it_provides_custom_error_messages()
    {
        // Create a mock request
        $request = new AddressRequest();

        // Get error messages
        $messages = $request->messages();

        // Assert some important error messages exist
        $this->assertArrayHasKey('country.required', $messages);
        $this->assertArrayHasKey('postalcode.required', $messages);
        $this->assertArrayHasKey('postalcode.regex', $messages);
        $this->assertArrayHasKey('city.required', $messages);
        $this->assertArrayHasKey('public_space_type.in', $messages);
    }

    #[Test]
    public function it_rejects_invalid_data()
    {
        // Create a validator with invalid data
        $validator = Validator::make(
            [
                'postalcode' => 'ABC', // Should be 4 digits
                'public_space_type' => 'invalid_type', // Not in the allowed list
            ],
            (new AddressRequest())->rules(),
            (new AddressRequest())->messages()
        );

        // Assert validation fails
        $this->assertTrue($validator->fails());

        // Assert specific validation errors
        $errors = $validator->errors();
        $this->assertTrue($errors->has('postalcode'));
        $this->assertTrue($errors->has('public_space_type'));
    }

    #[Test]
    public function it_accepts_valid_data()
    {
        // Create a validator with valid data
        $validator = Validator::make(
            [
                'country' => 'Magyarország',
                'postalcode' => 1151,
                'city' => 'Budapest',
                'road_name' => 'Esthajnal',
                'public_space_type' => 'utca',
                'building_number' => '3.',
            ],
            (new AddressRequest())->rules(),
            (new AddressRequest())->messages()
        );

        // Assert validation passes
        $this->assertFalse($validator->fails());
    }
}
