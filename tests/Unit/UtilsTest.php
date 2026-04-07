<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Utils\Utils;

class UtilsTest extends TestCase
{
    // ============================================
    // capitalize() TESTS
    // ============================================

    public function test_should_return_capitalized_string_when_all_lowercase(): void
    {
        // Arrange
        $input = "hello";
        
        // Act
        $result = Utils::capitalize($input);
        
        // Assert
        $this->assertEquals("Hello", $result);
    }

    public function test_should_return_capitalized_string_when_all_uppercase(): void
    {
        // Arrange
        $input = "WORLD";
        
        // Act
        $result = Utils::capitalize($input);
        
        // Assert
        $this->assertEquals("World", $result);
    }

    public function test_should_return_empty_string_when_input_is_empty(): void
    {
        // Arrange
        $input = "";
        
        // Act
        $result = Utils::capitalize($input);
        
        // Assert
        $this->assertEquals("", $result);
    }

    public function test_should_return_empty_string_when_input_is_null(): void
    {
        // Arrange
        $input = null;
        
        // Act
        $result = Utils::capitalize($input);
        
        // Assert
        $this->assertEquals("", $result);
    }


    // ============================================
    // calculateAverage() TESTS
    // ============================================

    public function test_should_return_average_when_array_has_multiple_elements(): void
    {
        // Arrange
        $input = [10, 12, 14];
        
        // Act
        $result = Utils::calculateAverage($input);
        
        // Assert
        $this->assertEquals(12.0, $result);
    }

    public function test_should_return_same_value_when_array_has_one_element(): void
    {
        // Arrange
        $input = [15];
        
        // Act
        $result = Utils::calculateAverage($input);
        
        // Assert
        $this->assertEquals(15.0, $result);
    }

    public function test_should_return_zero_when_array_is_empty(): void
    {
        // Arrange
        $input = [];
        
        // Act
        $result = Utils::calculateAverage($input);
        
        // Assert
        $this->assertEquals(0.0, $result);
    }

    public function test_should_return_rounded_average_when_result_has_decimals(): void
    {
        // Arrange
        $input = [10, 11, 12];
        
        // Act
        $result = Utils::calculateAverage($input);
        
        // Assert
        $this->assertEquals(11.0, $result);
    }

    public function test_should_return_zero_when_array_is_null(): void
    {
        // Arrange
        $input = null;
        
        // Act
        $result = Utils::calculateAverage($input);
        
        // Assert
        $this->assertEquals(0.0, $result);
    }

    public function test_should_handle_negative_numbers_when_calculating_average(): void
    {
        // Arrange
        $input = [-10, -5, 0];
        
        // Act
        $result = Utils::calculateAverage($input);
        
        // Assert
        $this->assertEquals(-5.0, $result);
    }


    // ============================================
    // slugify() TESTS
    // ============================================

    public function test_should_return_slug_when_string_has_uppercase_and_spaces(): void
    {
        // Arrange
        $input = "Hello World";
        
        // Act
        $result = Utils::slugify($input);
        
        // Assert
        $this->assertEquals("hello-world", $result);
    }

    public function test_should_return_slug_when_string_has_spaces_everywhere(): void
    {
        // Arrange
        $input = " Spaces Everywhere ";
        
        // Act
        $result = Utils::slugify($input);
        
        // Assert
        $this->assertEquals("spaces-everywhere", $result);
    }

    public function test_should_remove_apostrophes_and_special_chars_when_string_has_some(): void
    {
        // Arrange
        $input = "C'est l'ete !";
        
        // Act
        $result = Utils::slugify($input);
        
        // Assert
        $this->assertEquals("cest-lete", $result);
    }

    public function test_should_return_empty_string_when_input_is_empty_for_slugify(): void
    {
        // Arrange
        $input = "";
        
        // Act
        $result = Utils::slugify($input);
        
        // Assert
        $this->assertEquals("", $result);
    }

    public function test_should_return_empty_string_when_input_is_null_for_slugify(): void
    {
        // Arrange
        $input = null;
        
        // Act
        $result = Utils::slugify($input);
        
        // Assert
        $this->assertEquals("", $result);
    }


    // ============================================
    // clamp() TESTS
    // ============================================

    public function test_should_return_value_when_within_range(): void
    {
        // Arrange
        $value = 5;
        $min = 0;
        $max = 10;
        
        // Act
        $result = Utils::clamp($value, $min, $max);
        
        // Assert
        $this->assertEquals($value, $result);
    }

    public function test_should_return_min_when_value_is_below_min(): void
    {
        // Arrange
        $value = -5;
        $min = 0;
        $max = 10;
        
        // Act
        $result = Utils::clamp($value, $min, $max);
        
        // Assert
        $this->assertEquals($min, $result);
    }

    public function test_should_return_max_when_value_is_above_max(): void
    {
        // Arrange
        $value = 15;
        $min = 0;
        $max = 10;
        
        // Act
        $result = Utils::clamp($value, $min, $max);
        
        // Assert
        $this->assertEquals($max, $result);
    }

    public function test_should_return_zero_when_all_params_are_zero(): void
    {
        // Arrange
        $value = 0;
        $min = 0;
        $max = 0;
        
        // Act
        $result = Utils::clamp($value, $min, $max);
        
        // Assert
        $this->assertEquals(0, $result);
    }

    public function test_should_return_max_when_value_is_at_max_limit(): void
    {
        // Arrange
        $value = 10;
        $min = 0;
        $max = 10;
        
        // Act
        $result = Utils::clamp($value, $min, $max);
        
        // Assert
        $this->assertEquals(10, $result);
    }

    // ============================================
    // sortStudents() TESTS (TDD)
    // ============================================

    public function test_should_sort_students_by_grade_ascending(): void
    {
        $students = [
            ['name' => 'Alice', 'grade' => 15, 'age' => 20],
            ['name' => 'Bob', 'grade' => 12, 'age' => 22],
            ['name' => 'Charlie', 'grade' => 18, 'age' => 19],
        ];

        $expected = [
            ['name' => 'Bob', 'grade' => 12, 'age' => 22],
            ['name' => 'Alice', 'grade' => 15, 'age' => 20],
            ['name' => 'Charlie', 'grade' => 18, 'age' => 19],
        ];

        $result = Utils::sortStudents($students, 'grade', 'asc');
        $this->assertEquals($expected, $result);
    }

    public function test_should_sort_students_by_grade_descending(): void
    {
        $students = [
            ['name' => 'Alice', 'grade' => 15, 'age' => 20],
            ['name' => 'Bob', 'grade' => 12, 'age' => 22],
            ['name' => 'Charlie', 'grade' => 18, 'age' => 19],
        ];

        $expected = [
            ['name' => 'Charlie', 'grade' => 18, 'age' => 19],
            ['name' => 'Alice', 'grade' => 15, 'age' => 20],
            ['name' => 'Bob', 'grade' => 12, 'age' => 22],
        ];

        $result = Utils::sortStudents($students, 'grade', 'desc');
        $this->assertEquals($expected, $result);
    }

    public function test_should_sort_students_by_name_ascending(): void
    {
        $students = [
            ['name' => 'Charlie', 'grade' => 18, 'age' => 19],
            ['name' => 'Alice', 'grade' => 15, 'age' => 20],
            ['name' => 'Bob', 'grade' => 12, 'age' => 22],
        ];

        $expected = [
            ['name' => 'Alice', 'grade' => 15, 'age' => 20],
            ['name' => 'Bob', 'grade' => 12, 'age' => 22],
            ['name' => 'Charlie', 'grade' => 18, 'age' => 19],
        ];

        $result = Utils::sortStudents($students, 'name', 'asc');
        $this->assertEquals($expected, $result);
    }

    public function test_should_sort_students_by_age_ascending(): void
    {
        $students = [
            ['name' => 'Alice', 'grade' => 15, 'age' => 20],
            ['name' => 'Bob', 'grade' => 12, 'age' => 22],
            ['name' => 'Charlie', 'grade' => 18, 'age' => 19],
        ];

        $expected = [
            ['name' => 'Charlie', 'grade' => 18, 'age' => 19],
            ['name' => 'Alice', 'grade' => 15, 'age' => 20],
            ['name' => 'Bob', 'grade' => 12, 'age' => 22],
        ];

        $result = Utils::sortStudents($students, 'age', 'asc');
        $this->assertEquals($expected, $result);
    }

    public function test_should_return_empty_array_for_null_input(): void
    {
        $result = Utils::sortStudents(null, 'name');
        $this->assertEquals([], $result);
    }

    public function test_should_return_empty_array_for_empty_input(): void
    {
        $result = Utils::sortStudents([], 'name');
        $this->assertEquals([], $result);
    }

    public function test_should_not_modify_the_original_array(): void
    {
        $original = [
            ['name' => 'Bob', 'grade' => 12, 'age' => 22],
            ['name' => 'Alice', 'grade' => 15, 'age' => 20],
        ];
        
        $copy = [
            ['name' => 'Bob', 'grade' => 12, 'age' => 22],
            ['name' => 'Alice', 'grade' => 15, 'age' => 20],
        ];

        Utils::sortStudents($original, 'name', 'asc');
        $this->assertEquals($copy, $original);
    }

    public function test_should_default_to_ascending_order(): void
    {
        $students = [
            ['name' => 'Charlie', 'grade' => 18, 'age' => 19],
            ['name' => 'Alice', 'grade' => 15, 'age' => 20],
            ['name' => 'Bob', 'grade' => 12, 'age' => 22],
        ];

        $expected = [
            ['name' => 'Alice', 'grade' => 15, 'age' => 20],
            ['name' => 'Bob', 'grade' => 12, 'age' => 22],
            ['name' => 'Charlie', 'grade' => 18, 'age' => 19],
        ];

        $result = Utils::sortStudents($students, 'name');
        $this->assertEquals($expected, $result);
    }
}
