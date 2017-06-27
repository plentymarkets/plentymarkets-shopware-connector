<?php

namespace PlentyConnector\tests\Unit\Adapter\PlentymarketsAdapter\RequestGenerator;

use PHPUnit\Framework\TestCase;
use PlentyConnector\Connector\IdentityService\IdentityService;
use PlentyConnector\Connector\IdentityService\Model\Identity;
use Ramsey\Uuid\Uuid;

/**
 * Class RequestGeneratorTest
 */
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

    public function setup()
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
        $identityService->expects($this->any())->method('isMapppedIdentity')->willReturn(true);

        $this->identityService = $identityService;
    }
}
