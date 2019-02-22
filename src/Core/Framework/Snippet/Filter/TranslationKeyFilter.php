<?php declare(strict_types=1);

namespace Shopware\Core\Framework\Snippet\Filter;

class TranslationKeyFilter extends AbstractFilter implements SnippetFilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'translationKey';
    }

    /**
     * {@inheritdoc}
     */
    public function filter(array $snippets, $requestFilterValue, array $additionalData = []): array
    {
        if (empty($requestFilterValue) || !is_array($requestFilterValue)) {
            return $snippets;
        }

        $result = [];
        foreach ($snippets as $setId => $set) {
            foreach ($set['snippets'] as $translationKey => $snippet) {
                if (!in_array($translationKey, $requestFilterValue, true)) {
                    continue;
                }
                $result[$setId]['snippets'][$translationKey] = $snippet;
            }
        }

        return $this->readjust($result, $snippets);
    }
}
