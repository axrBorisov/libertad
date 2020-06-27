<?php

namespace core\forms\manage\testimonials;

use core\entities\Testimonials\Testimonials;
use core\helpers\TestimonialsHelper;
use yii\base\Model;

class TestimonialsForm extends Model
{
    public $name;
    public $image;
    public $position;
    public $body;

    public function __construct(Testimonials $testimonials = null, $config = [])
    {
        if ($testimonials) {
            $this->name = $testimonials->name;
            $this->image = $testimonials->image;
            $this->position = $testimonials->position;
            $this->body = $testimonials->body;
        }
        parent::__construct($config);
    }

    public function rules(): array
    {
        return [
            [['name', 'body', 'image'], 'required',],
            [['name', 'position', 'image'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels(): array
    {
        return TestimonialsHelper::attributeLabels();
    }
}