<?php

namespace App\Serializer;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class ApiNormalizer implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface
{
    private $decorated;
    private $router;

    public function __construct(NormalizerInterface $decorated, RouterInterface $router)
    {
        if (!$decorated instanceof DenormalizerInterface) {
            throw new \InvalidArgumentException(sprintf('The decorated normalizer must implement the %s.', DenormalizerInterface::class));
        }

        $this->decorated = $decorated;
        $this->router = $router;
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $this->decorated->supportsNormalization($data, $format);
    }

    public function normalize($object, $format = null, array $context = []): array
    {
        $data = $this->decorated->normalize($object, $format, $context);

        if (is_array($data)) {
            if ($object instanceof \App\Entity\CareRequest) {
                $data['relatedUri'] = [
                    'getHtmlForm' => $this->router->generate('care_request_form', ['id' => $object->getId()]),
                ];
            }

            if ($object instanceof \App\Entity\Comment) {
                $data['relatedUri'] = [
                    'getHtmlContent' => $this->router->generate('comment', ['id' => $object->getId()]),
                ];
            }
        }

        return $data;
    }

    public function supportsDenormalization($data, $type, $format = null): bool
    {
        return $this->decorated->supportsDenormalization($data, $type, $format);
    }

    public function denormalize($data, $class, $format = null, array $context = []): mixed
    {
        return $this->decorated->denormalize($data, $class, $format, $context);
    }

    public function setSerializer(SerializerInterface $serializer)
    {
        if($this->decorated instanceof SerializerAwareInterface) {
            $this->decorated->setSerializer($serializer);
        }
    }
}