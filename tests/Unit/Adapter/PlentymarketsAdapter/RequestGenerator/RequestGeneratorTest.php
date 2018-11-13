<?php

namespace PlentyConnector\tests\Unit\Adapter\PlentymarketsAdapter\RequestGenerator;

use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use SystemConnector\IdentityService\IdentityService;
use SystemConnector\ValueObject\Identity\Identity;

abstract class RequestGeneratorTest extends TestCase
{
    /**
     * @var IdentityService
     */
    protected $identityService;

    /**
     * @var string
     */
    protected $objectIdentifier;

    protected function setUp()
    {
        $this->objectIdentifier = Uuid::uuid4()->toString();

        $identity = $this->createMock(Identity::class);
        $identity->method('getObjectIdentifier')->willReturn($this->objectIdentifier);
        $identity->method('getAdapterIdentifier')->willReturn('1');

        /**
         * @var IdentityService|\PHPUnit_Framework_MockObject_MockObject $identityService
         */
        $identityService = $this->createMock(IdentityService::class);
        $identityService->method('findOneBy')->willReturn($identity);
        $identityService->method('findOneOrThrow')->willReturn($identity);
        $identityService->method('findOneOrCreate')->willReturn($identity);
        $identityService->method('isMappedIdentity')->willReturn(true);

        $this->identityService = $identityService;
    }
}
