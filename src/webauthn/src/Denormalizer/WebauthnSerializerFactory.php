<?php

declare(strict_types=1);

namespace Webauthn\Denormalizer;

use RuntimeException;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\UidNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;

final class WebauthnSerializerFactory
{
    public function __construct(
        private readonly AttestationStatementSupportManager $attestationStatementSupportManager
    ) {
    }

    public function create(): SerializerInterface
    {
        foreach (self::getRequiredSerializerClasses() as $class => $package) {
            if (! class_exists($class)) {
                throw new RuntimeException(sprintf(
                    'The class "%s" is required. Please install the package "%s" to use this feature.',
                    $class,
                    $package
                ));
            }
        }

        $denormalizers = [
            new AttestationObjectDenormalizer(),
            new AttestationStatementDenormalizer($this->attestationStatementSupportManager),
            new AuthenticationExtensionsClientInputsDenormalizer(),
            new AuthenticatorAssertionResponseDenormalizer(),
            new AuthenticatorAttestationResponseDenormalizer(),
            new AuthenticatorDataDenormalizer(),
            new AuthenticatorResponseDenormalizer(),
            new CollectedClientDataDenormalizer(),
            new PublicKeyCredentialDenormalizer(),
            new PublicKeyCredentialOptionsDenormalizer(),
            new PublicKeyCredentialSourceDenormalizer(),
            new PublicKeyCredentialUserEntityDenormalizer(),
            new UidNormalizer(),
            new ArrayDenormalizer(),
            new ObjectNormalizer(
                propertyTypeExtractor: new PropertyInfoExtractor(typeExtractors: [
                    new PhpDocExtractor(),
                    new ReflectionExtractor(),
                ])
            ),
        ];

        return new Serializer($denormalizers, [new JsonEncoder()]);
    }

    /**
     * @return array<class-string, string>
     */
    private static function getRequiredSerializerClasses(): array
    {
        return [
            UidNormalizer::class => 'symfony/serializer',
            ArrayDenormalizer::class => 'symfony/serializer',
            ObjectNormalizer::class => 'symfony/serializer',
            PropertyInfoExtractor::class => 'symfony/serializer',
            PhpDocExtractor::class => 'phpdocumentor/reflection-docblock',
            ReflectionExtractor::class => 'symfony/serializer',
            JsonEncoder::class => 'symfony/serializer',
            Serializer::class => 'symfony/serializer',
        ];
    }
}