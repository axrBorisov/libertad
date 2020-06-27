<?php

namespace core\entities\Testimonials;

use core\helpers\TestimonialsHelper;
use yii\db\ActiveRecord;

/**
 * @property integer $id
 *
 * @property string $name
 * @property string $image
 * @property string $position
 * @property string $body
 *
 */
class Testimonials extends ActiveRecord
{
    public $meta;

    public static function create($name, $image, $position, $body): self
    {
        $testimonials = new static();
        $testimonials->name = $name;
        $testimonials->image = $image;
        $testimonials->position = $position;
        $testimonials->body = $body;
        return $testimonials;
    }

    public function edit($name, $image, $position, $body): void
    {
        $this->name = $name;
        $this->image = $image;
        $this->position = $position;
        $this->body = $body;
    }

    public function showAll()
    {
        $testimonials = Testimonials::find()->asArray()->all();

        if (empty($testimonials) || $testimonials === null) {
            echo '-';
        } else {

            foreach ($testimonials as $array => $testimonial) {
                echo '<div class="testimonial-item">'
                    . '<img src="/files/testimonials/' . $testimonial['image'] . '" class="testimonial-img" alt="">'
                    . '<h3>' . $testimonial['name'] . '</h3>'
                    . '<h4>' . $testimonial['position'] . '</h4>'
                    . '<p> ' . $testimonial['body'] . '</p>'
                    . '</div>';
            }
        }
    }

    public function attributeLabels(): array
    {
        return TestimonialsHelper::attributeLabels();
    }

    public static function tableName(): string
    {
        return '{{%testimonials}}';
    }
}