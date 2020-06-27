<?php

namespace core\useCases\manage;

use core\entities\Testimonials\Testimonials;
use core\forms\manage\testimonials\TestimonialsForm;
use core\repositories\TestimonialsRepository;

class TestimonialsService
{
    private $repository;

    public function __construct(TestimonialsRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create(TestimonialsForm $form): Testimonials
    {
        $testimonials = Testimonials::create(
            $form->name,
            $form->image,
            $form->position,
            $form->body
        );
        $this->repository->save($testimonials);
        return $testimonials;
    }

    public function edit(Testimonials $testimonials, TestimonialsForm $form): void
    {
        $testimonials->edit(
            $form->name,
            $form->image,
            $form->position,
            $form->body
        );
        $this->repository->save($testimonials);
    }

    public function remove(int $id): void
    {
        $testimonials = $this->repository->get($id);
        $this->repository->remove($testimonials);
    }
}