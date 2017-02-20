<?php

namespace PlentyConnector\tests\Unit\TranslationHelper;

use PHPUnit\Framework\TestCase;
use PlentyConnector\Connector\TransferObject\Product\Product;
use PlentyConnector\Connector\Translation\TranslationHelper;
use PlentyConnector\Connector\ValueObject\Translation\Translation;
use Ramsey\Uuid\Uuid;

/**
 * Class TranslationHelperTest
 */
class TranslationHelperTest extends TestCase
{
    public function test_can_translate_generic_transfer_object()
    {
        $languageIdentifier = Uuid::uuid4()->toString();
        $testValue = 'testTranslated';

        $translations = [Translation::fromArray([
            'languageIdentifier' => $languageIdentifier,
            'property' => 'name',
            'value' => $testValue,
        ])];

        $mockObject = $this->createMock(Product::class);
        $mockObject->expects($this->once())->method('getTranslations')->willReturn($translations);
        $mockObject->expects($this->once())->method('setName')->with($testValue);

        $helper = new TranslationHelper();
        $helper->translate($languageIdentifier, $mockObject);
    }
}
