<?php

namespace PlentymarketsAdapter\Helper;

use PlentymarketsAdapter\PlentymarketsAdapter;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\TransferObject\Language\Language;

class LanguageHelper implements LanguageHelperInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @param IdentityServiceInterface $identityService
     */
    public function __construct(
        IdentityServiceInterface $identityService
    ) {
        $this->identityService = $identityService;
    }

    /**
     * @return array
     */
    public function getLanguages(): array
    {
        return [
            [
                'id' => 'bg',
                'name' => 'Bulgarian',
            ],
            [
                'id' => 'cn',
                'name' => 'Chinese',
            ],
            [
                'id' => 'cz',
                'name' => 'Czech',
            ],
            [
                'id' => 'da',
                'name' => 'Danish',
            ],
            [
                'id' => 'de',
                'name' => 'German',
            ],
            [
                'id' => 'en',
                'name' => 'English',
            ],
            [
                'id' => 'es',
                'name' => 'Spanish',
            ],
            [
                'id' => 'fr',
                'name' => 'French',
            ],
            [
                'id' => 'it',
                'name' => 'Italian',
            ],
            [
                'id' => 'nl',
                'name' => 'Dutch',
            ],
            [
                'id' => 'nn',
                'name' => 'Norwegian',
            ],
            [
                'id' => 'pl',
                'name' => 'Polish',
            ],
            [
                'id' => 'pt',
                'name' => 'Portuguese',
            ],
            [
                'id' => 'ro',
                'name' => 'Romanian',
            ],
            [
                'id' => 'ru',
                'name' => 'Russian',
            ],
            [
                'id' => 'se',
                'name' => 'Swedish',
            ],
            [
                'id' => 'sk',
                'name' => 'Slovak',
            ],
            [
                'id' => 'tr',
                'name' => 'Turkish',
            ],
            [
                'id' => 'vn',
                'name' => 'Vietnamese',
            ],
        ];
    }

    /**
     * @return string
     */
    public function getLanguagesQueryString(): string
    {
        $languages = [];

        foreach ($this->getLanguages() as $language) {
            $languageIdentity = $this->identityService->findOneBy([
                'adapterIdentifier' => (string) $language['id'],
                'adapterName' => PlentymarketsAdapter::NAME,
                'objectType' => Language::TYPE,
            ]);

            if ($languageIdentity === null) {
                continue;
            }

            $isMapped = $this->identityService->isMappedIdentity(
                $languageIdentity->getObjectIdentifier(),
                Language::TYPE,
                PlentymarketsAdapter::NAME
            );

            if ($isMapped) {
                $languages[] = $language['id'];
            }
        }

        return implode(',', $languages);
    }
}
