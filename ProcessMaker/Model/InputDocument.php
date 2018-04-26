<?php

namespace ProcessMaker\Model;

use Illuminate\Database\Eloquent\Model;
use Watson\Validating\ValidatingTrait;

/**
 * Class InputDocument
 * @package ProcessMaker\Model
 *
 * @property string INP_DOC_UID
 * @property int PRO_ID
 * @property string PRO_UID
 * @property string INP_DOC_TITLE
 * @property string INP_DOC_DESCRIPTION
 * @property string INP_DOC_FORM_NEEDED
 * @property string INP_DOC_ORIGINAL
 * @property string INP_DOC_PUBLISHED
 * @property int INP_DOC_VERSIONING
 * @property string INP_DOC_DESTINATION_PATH
 * @property string INP_DOC_TAGS
 * @property string INP_DOC_TYPE_FILE
 * @property int INP_DOC_MAX_FILESIZE
 * @property string INP_DOC_MAX_FILESIZE_UNIT
 * 
 */
class InputDocument extends Model
{
    use ValidatingTrait;

    protected $table = 'INPUT_DOCUMENT';
    protected $primaryKey = 'INP_DOC_ID';

    public $timestamps = false;

    /**
     * Values for INP_DOC_FORM_NEEDED
     */
    const FORM_NEEDED_TYPE = [
        'VIRTUAL' => 'Digital',
        'REAL' => 'Printed',
        'VREAL' => 'Digital/Printed'
    ];

    /**
     * Values for INP_DOC_ORIGINAL
     */
    const DOC_ORIGINAL_TYPE = [
        'ORIGINAL',
        'COPY',
        'COPYLEGAL'
    ];

    /**
     * Values for INP_DOC_PUBLISHED
     */
    const DOC_PUBLISHED_TYPE = [
        'PRIVATE',
        'PUBLIC'
    ];

    /**
     * Values for INP_DOC_TAGS
     */
    const DOC_TAGS_TYPE = [
        'INPUT'
    ];

    protected $fillable = [
        'INP_DOC_UID',
        'PRO_ID',
        'PRO_UID',
        'INP_DOC_TITLE',
        'INP_DOC_DESCRIPTION',
        'INP_DOC_FORM_NEEDED',
        'INP_DOC_ORIGINAL',
        'INP_DOC_PUBLISHED',
        'INP_DOC_VERSIONING',
        'INP_DOC_DESTINATION_PATH',
        'INP_DOC_TAGS',
        'INP_DOC_TYPE_FILE',
        'INP_DOC_MAX_FILESIZE',
        'INP_DOC_MAX_FILESIZE_UNIT'
    ];

    protected $attributes = [
        'INP_DOC_UID' => null,
        'PRO_ID' => '',
        'PRO_UID' => null,
        'INP_DOC_TITLE' => null,
        'INP_DOC_DESCRIPTION' => null,
        'INP_DOC_FORM_NEEDED' => 'REAL',
        'INP_DOC_ORIGINAL' => 'COPY',
        'INP_DOC_PUBLISHED' => 'PRIVATE',
        'INP_DOC_VERSIONING' => 0,
        'INP_DOC_DESTINATION_PATH' => null,
        'INP_DOC_TAGS' => null,
        'INP_DOC_TYPE_FILE' => '*.*',
        'INP_DOC_MAX_FILESIZE' => 0,
        'INP_DOC_MAX_FILESIZE_UNIT' => 'KB'
    ];

    protected $casts = [
        'INP_DOC_UID' => 'string',
        'PRO_ID' => 'int',
        'PRO_UID' => 'string',
        'INP_DOC_TITLE' => 'string',
        'INP_DOC_DESCRIPTION' => 'string',
        'INP_DOC_FORM_NEEDED' => 'string',
        'INP_DOC_ORIGINAL' => 'string',
        'INP_DOC_PUBLISHED' => 'string',
        'INP_DOC_VERSIONING' => 'int',
        'INP_DOC_DESTINATION_PATH' => 'string',
        'INP_DOC_TAGS' => 'string',
        'INP_DOC_TYPE_FILE' => 'string',
        'INP_DOC_MAX_FILESIZE' => 'int',
        'INP_DOC_MAX_FILESIZE_UNIT' => 'string'
    ];

    protected $rules = [
        'INP_DOC_UID' => 'required|max:32',
        'INP_DOC_TITLE' => 'required|unique:INPUT_DOCUMENT,INP_DOC_TITLE',
        'PRO_ID' => 'required',
        'PRO_UID' => 'required|max:32',
        'INP_DOC_VERSIONING' => 'required|boolean'
    ];

    protected $validationMessages = [
        'INP_DOC_TITLE.unique' => 'A Input Document with the same name already exists in this process.'
    ];

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'INP_DOC_UID';
    }

    /**
     * Get the translation of Type form needed
     *
     * @param string $value
     *
     * @return string
     */
    public function getInpDocFormNeededAttribute($value): ?string
    {
        return __(self::FORM_NEEDED_TYPE[$value]);
    }
}
