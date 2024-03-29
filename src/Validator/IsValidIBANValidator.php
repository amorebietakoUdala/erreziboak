<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use PHP_IBAN\IBAN;

class IsValidIBANValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        /** @var App\Validator\IBAN $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        if (!$this->validateIBAN($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{value}', $value)
                ->addViolation();
        }
    }

    public function validateIBAN(string $ibanNumber): bool
    {
        $iban = new IBAN($ibanNumber);
        $valid = $iban->Verify();

        return $valid;
    }
}
