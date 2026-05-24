import { AbstractControl, ValidationErrors, ValidatorFn } from '@angular/forms';

/**
 * Strict email validator that requires a valid domain extension.
 * This validator ensures the email has:
 * - A valid local part (before @)
 * - A domain with at least one dot
 * - A TLD (top-level domain) with at least 2 characters
 * 
 * Example valid emails:
 * - user@example.com
 * - test@domain.co.uk
 * 
 * Example invalid emails:
 * - user@domain (no TLD)
 * - user@ (incomplete)
 */
export function strictEmailValidator(): ValidatorFn {
  return (control: AbstractControl): ValidationErrors | null => {
    if (!control.value) {
      return null; // Empty values are handled by required validator
    }

    const email = control.value as string;
    
    // First check: must contain @ symbol
    if (!email.includes('@')) {
      return { invalidEmail: { value: email } };
    }

    const parts = email.split('@');
    if (parts.length !== 2) {
      return { invalidEmail: { value: email } };
    }

    const [localPart, domain] = parts;

    // Check local part is not empty
    if (!localPart || localPart.length === 0) {
      return { invalidEmail: { value: email } };
    }

    // Check domain contains at least one dot (for TLD)
    if (!domain.includes('.')) {
      return { invalidEmail: { value: email } };
    }

    // Check domain has TLD (at least 2 characters after the last dot)
    const lastDotIndex = domain.lastIndexOf('.');
    const tld = domain.substring(lastDotIndex + 1);
    if (tld.length < 2) {
      return { invalidEmail: { value: email } };
    }

    // Final regex validation for proper format
    // This ensures: local@domain.tld format with valid characters
    const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    if (!emailPattern.test(email)) {
      return { invalidEmail: { value: email } };
    }

    return null;
  };
}

