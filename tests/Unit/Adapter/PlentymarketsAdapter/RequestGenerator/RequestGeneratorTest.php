<?php

namespace PlentyConnector\tests\Unit\Adapter\PlentymarketsAdapter\RequestGenerator;

use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use SystemConnector\IdentityService\IdentityService;
use SystemConnector\IdentityService\Model\Identity;

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
        $identity->expects($this->any())->method('getObjectIdentifier')->willReturn($this->objectIdentifier);
        $identity->expects($this->any())->method('getAdapterIdentifier')->willReturn('1');

        /**
         * @var IdentityService|\PHPUnit_Framework_MockObject_MockObject $identityService
         */
        $identityService = $this->createMock(IdentityService::class);
        $identityService->expects($this->any())->method('findOneBy')->willReturn($identity);
        $identityService->expects($this->any())->method('findOneOrThrow')->willReturn($identity);
        $identityService->expects($this->any())->method('findOneOrCreate')->willReturn($identity);
        $identityService->expects($this->any())->method('isMappedIdentity')->willReturn(true);

        $this->identityService = $identityService;
    }
}
