<?php

namespace App\Models\Traits;

use App\Models\Media;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Trait Mediable
 *
 * Agrega soporte de archivos media polimórficos a cualquier modelo.
 *
 * Uso:
 *   class Content extends Model {
 *       use Mediable;
 *   }
 *
 *   $content->media             // colección de Media
 *   $content->images            // solo imágenes
 *   $content->attachMedia($id)  // asociar archivo existente
 *   $content->detachMedia($id)  // desasociar archivo
 */
trait Mediable
{
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable', 'medi_nmmdbl', 'medi_idmdbl');
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable', 'medi_nmmdbl', 'medi_idmdbl')
                    ->where('medi_cdtype', 'like', 'image/%');
    }

    public function videos(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable', 'medi_nmmdbl', 'medi_idmdbl')
                    ->where('medi_cdtype', 'like', 'video/%');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable', 'medi_nmmdbl', 'medi_idmdbl')
                    ->where('medi_cdtype', 'application/pdf');
    }

    /**
     * Asocia un archivo media existente a este modelo.
     */
    public function attachMedia(int $mediaId): void
    {
        Media::where('medi_idmedi', $mediaId)->update([
            'medi_idmdbl' => $this->getKey(),
            'medi_nmmdbl' => static::class,
        ]);
    }

    /**
     * Desasocia un archivo media de este modelo.
     */
    public function detachMedia(int $mediaId): void
    {
        Media::where('medi_idmedi', $mediaId)
             ->where('medi_idmdbl', $this->getKey())
             ->where('medi_nmmdbl', static::class)
             ->update([
                 'medi_idmdbl' => null,
                 'medi_nmmdbl' => null,
             ]);
    }

    /**
     * Verifica si el modelo tiene archivos media asociados.
     */
    public function hasMedia(): bool
    {
        return $this->media()->exists();
    }
}
