<?php

namespace App\Services\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class HierarchyTreeService
{
    /**
     * @return Collection<int, array{
     *     item: Model,
     *     depth: int,
     *     parent_id: int|null,
     *     path: string
     * }>
     */
    public function rows(Collection $nodes, string $labelField = 'name'): Collection
    {
        $byParent = $nodes->groupBy(
            fn (Model $node): string => (string) ($node->getAttribute('parent_id') ?? 0)
        );

        $rows = collect();
        $visited = [];

        $append = function (
            Model $node,
            int $depth,
            string $parentPath,
        ) use (&$append, $byParent, $rows, &$visited, $labelField): void {
            $id = (int) $node->getKey();

            if (isset($visited[$id])) {
                return;
            }

            $visited[$id] = true;

            $label = (string) $node->getAttribute($labelField);
            $path = $parentPath === '' ? $label : $parentPath.' / '.$label;

            $rows->push([
                'item' => $node,
                'depth' => $depth,
                'parent_id' => $node->getAttribute('parent_id')
                    ? (int) $node->getAttribute('parent_id')
                    : null,
                'path' => $path,
            ]);

            foreach ($byParent->get((string) $id, collect()) as $child) {
                $append($child, $depth + 1, $path);
            }
        };

        foreach ($byParent->get('0', collect()) as $root) {
            $append($root, 0, '');
        }

        foreach ($nodes as $node) {
            if (! isset($visited[(int) $node->getKey()])) {
                $append($node, 0, '');
            }
        }

        return $rows;
    }
}
