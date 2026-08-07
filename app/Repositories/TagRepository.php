<?php

declare(strict_types=1);

namespace App\Repositories;

/**
 * Tag data access layer.
 *
 * Following php-pro skill: Repository pattern
 * Following database-optimizer skill: Indexed queries
 */
class TagRepository extends BaseRepository
{
    protected string $table = 'cms_tags';

    /** @var list<string> */
    protected array $columns = [
        'id', 'name', 'slug', 'description', 'color', 'created_at', 'updated_at',
    ];

    /**
     * Find a tag by slug.
     *
     * @param string $slug
     * @return array<string, mixed>|false
     */
    public function findBySlug(string $slug): array|false
    {
        return $this->db->fetch(
            "SELECT * FROM {$this->table} WHERE slug = ?",
            [$slug]
        );
    }

    /**
     * Get all tags with post counts.
     *
     * @return list<array<string, mixed>>
     */
    public function findAllWithCounts(): array
    {
        return $this->db->fetchAll(
            "SELECT t.*, COUNT(pt.post_id) as post_count
             FROM {$this->table} t
             LEFT JOIN cms_post_tags pt ON t.id = pt.tag_id
             GROUP BY t.id
             ORDER BY t.name"
        );
    }

    /**
     * Get tags for a specific post.
     *
     * @param int $postId
     * @return list<array<string, mixed>>
     */
    public function findByPostId(int $postId): array
    {
        return $this->db->fetchAll(
            "SELECT t.* FROM {$this->table} t
             INNER JOIN cms_post_tags pt ON t.id = pt.tag_id
             WHERE pt.post_id = ?
             ORDER BY t.name",
            [$postId]
        );
    }

    /**
     * Create a tag with auto-generated slug.
     *
     * @param string $name
     * @param string|null $description
     * @param string $color
     * @return int
     */
    public function createWithSlug(string $name, ?string $description = null, string $color = '#3b82f6'): int
    {
        $slug = $this->generateUniqueSlug($name);
        return $this->create([
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'color' => $color,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Generate a unique slug for a tag.
     *
     * @param string $name
     * @return string
     */
    private function generateUniqueSlug(string $name): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $name), '-'));
        $baseSlug = $slug;
        $counter = 1;

        while ($this->findBySlug($slug) !== false) {
            $slug = $baseSlug . '-' . $counter++;
        }

        return $slug;
    }

    /**
     * Get or create a tag by name.
     *
     * @param string $name
     * @return int Tag ID
     */
    public function findOrCreate(string $name): int
    {
        $tag = $this->findBySlug(strtolower($name));
        if ($tag !== false) {
            return $tag['id'];
        }

        return $this->createWithSlug($name);
    }

    /**
     * Delete a tag and its associations.
     *
     * @param int $id
     * @return int
     */
    public function deleteWithAssociations(int $id): int
    {
        $this->db->delete('cms_post_tags', 'tag_id = ?', [$id]);
        return $this->delete($id);
    }

    /**
     * Search tags by name.
     *
     * @param string $query
     * @return list<array<string, mixed>>
     */
    public function search(string $query): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE name LIKE ? OR slug LIKE ? ORDER BY name",
            ['%' . $query . '%', '%' . $query . '%']
        );
    }
}
