<?php

declare(strict_types=1);

class ContentBlockModel extends AbstractSaveModel
{
    use ObfuscatorTrait;

    #[Override]
    protected function validateData(array $data): void
    {
    }

    #[Override]
    protected function generateMissingFields(array $data): array
    {
        // if (isset($data['block_type'])) {
        //     $blockType = BlockType::tryFrom($data['block_type']);
        //     if ($blockType === Blocktype::SUMMER_BANNER) {
        //         $data = ;
        //     }
        // }
        // if (!empty($data['image_url'])) {
        //     $data['block_metadata']['image']['url'] = $data['image_url'];
        //     unset($data['image_url']);
        // }
        return $this->prepareBlockMetadata($data);
    }

    // In your ContentBlockModel
    private function prepareBlockMetadata(array $rawData): array
    {
        // Start with existing block_metadata or empty array
        $mergedBlockMetadata = isset($rawData['block_metadata']) && is_array($rawData['block_metadata'])
            ? $rawData['block_metadata']
            : [];

        // Process flattened keys and OVERWRITE instead of merge
        foreach ($rawData as $key => $value) {
            if (preg_match('/^block_metadata\[([^\]]+)\]\[([^\]]+)\]$/', $key, $matches)) {
                $group = $matches[1];
                $subKey = $matches[2];

                // Extract value from array wrapper
                $value = (is_array($value) && count($value) === 1) ? $value[0] : $value;

                // Initialize group if not exists
                if (!isset($mergedBlockMetadata[$group])) {
                    $mergedBlockMetadata[$group] = [];
                }

                // OVERWRITE the value (don't merge)
                $mergedBlockMetadata[$group][$subKey] = $value;
            }
        }

        $mergedBlockMetadata = $this->cleanArrayWrappers($mergedBlockMetadata);

        // Create clean result array
        $result = [];
        foreach ($rawData as $key => $value) {
            if (!str_starts_with($key, 'block_metadata[') && $key !== 'block_metadata') {
                $result[$key] = $value;
            }
        }
        $result['block_metadata'] = $mergedBlockMetadata;
        return $result;
    }

    private function cleanArrayWrappers(array $array): array
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                // If it's a sequential array with one element, unwrap it
                if (array_is_list($value) && count($value) === 1) {
                    $array[$key] = $value[0];
                }
                // If it's a sequential array with multiple values (from conflicts)
                elseif (array_is_list($value) && count($value) > 1) {
                    // Take the last value (most recent)
                    $array[$key] = end($value);
                }
                // Recursively clean
                else {
                    $array[$key] = $this->cleanArrayWrappers($value);
                }
            }
        }
        return $array;
    }
}