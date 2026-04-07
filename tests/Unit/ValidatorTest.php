<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Utils\Validator;

class ValidatorTest extends TestCase
{
    // ============================================
    // isValidEmail() TESTS
    // ============================================

    public function test_should_return_true_when_email_is_valid(): void
    {
        // Act & Assert
        $this->assertTrue(Validator::isValidEmail("user@example.com"));
    }

    public function test_should_return_true_when_email_has_tags_and_dots(): void
    {
        // Act & Assert
        $this->assertTrue(Validator::isValidEmail("user.name+tag@domain.co"));
    }

    public function test_should_return_false_when_email_is_invalid_string(): void
    {
        // Act & Assert
        $this->assertFalse(Validator::isValidEmail("invalid"));
    }

    public function test_should_return_false_when_email_lacks_username(): void
    {
        // Act & Assert
        $this->assertFalse(Validator::isValidEmail("@domain.com"));
    }

    public function test_should_return_false_when_email_lacks_domain(): void
    {
        // Act & Assert
        $this->assertFalse(Validator::isValidEmail("user@"));
    }

    public function test_should_return_false_when_email_is_empty(): void
    {
        // Act & Assert
        $this->assertFalse(Validator::isValidEmail(""));
    }

    public function test_should_return_false_when_email_is_null(): void
    {
        // Act & Assert
        $this->assertFalse(Validator::isValidEmail(null));
    }

    // ============================================
    // isValidPassword() TESTS
    // ============================================

    public function test_should_return_valid_when_password_meets_all_criteria(): void
    {
        // Act
        $result = Validator::isValidPassword("Passw0rd!");
        
        // Assert
        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
    }

    public function test_should_return_invalid_when_password_is_short_and_lacks_characteristics(): void
    {
        // Act
        $result = Validator::isValidPassword("short");
        
        // Assert
        $this->assertFalse($result['valid']);
        $this->assertContains('Minimum 8 caracteres', $result['errors']);
        $this->assertContains('Au moins 1 majuscule', $result['errors']);
        $this->assertContains('Au moins 1 chiffre', $result['errors']);
        $this->assertContains('Au moins 1 caractere special', $result['errors']);
    }

    public function test_should_return_invalid_when_password_lacks_uppercase(): void
    {
        // Act
        $result = Validator::isValidPassword("alllowercase1!");
        
        // Assert
        $this->assertFalse($result['valid']);
        $this->assertContains('Au moins 1 majuscule', $result['errors']);
    }

    public function test_should_return_invalid_when_password_lacks_lowercase(): void
    {
        // Act
        $result = Validator::isValidPassword("ALLUPPERCASE1!");
        
        // Assert
        $this->assertFalse($result['valid']);
        $this->assertContains('Au moins 1 minuscule', $result['errors']);
    }

    public function test_should_return_invalid_when_password_lacks_digit(): void
    {
        // Act
        $result = Validator::isValidPassword("NoDigits!here");
        
        // Assert
        $this->assertFalse($result['valid']);
        $this->assertContains('Au moins 1 chiffre', $result['errors']);
    }

    public function test_should_return_invalid_when_password_lacks_special_char(): void
    {
        // Act
        $result = Validator::isValidPassword("NoSpecial1here");
        
        // Assert
        $this->assertFalse($result['valid']);
        $this->assertContains('Au moins 1 caractere special', $result['errors']);
    }

    public function test_should_return_all_errors_when_password_is_empty(): void
    {
        // Act
        $result = Validator::isValidPassword("");
        
        // Assert
        $this->assertFalse($result['valid']);
        $this->assertCount(5, $result['errors']);
    }

    public function test_should_return_all_errors_when_password_is_null(): void
    {
        // Act
        $result = Validator::isValidPassword(null);
        
        // Assert
        $this->assertFalse($result['valid']);
        $this->assertCount(5, $result['errors']);
    }

    // ============================================
    // isValidAge() TESTS
    // ============================================

    public function test_should_return_true_when_age_is_within_normal_range(): void
    {
        // Act & Assert
        $this->assertTrue(Validator::isValidAge(25));
    }

    public function test_should_return_true_when_age_is_minimum_boundary(): void
    {
        // Act & Assert
        $this->assertTrue(Validator::isValidAge(0));
    }

    public function test_should_return_true_when_age_is_maximum_boundary(): void
    {
        // Act & Assert
        $this->assertTrue(Validator::isValidAge(150));
    }

    public function test_should_return_false_when_age_is_negative(): void
    {
        // Act & Assert
        $this->assertFalse(Validator::isValidAge(-1));
    }

    public function test_should_return_false_when_age_is_above_max(): void
    {
        // Act & Assert
        $this->assertFalse(Validator::isValidAge(151));
    }

    public function test_should_return_false_when_age_is_float(): void
    {
        // Act & Assert
        $this->assertFalse(Validator::isValidAge(25.5));
    }

    public function test_should_return_false_when_age_is_string(): void
    {
        // Act & Assert
        $this->assertFalse(Validator::isValidAge("25"));
    }

    public function test_should_return_false_when_age_is_null(): void
    {
        // Act & Assert
        $this->assertFalse(Validator::isValidAge(null));
    }
}
