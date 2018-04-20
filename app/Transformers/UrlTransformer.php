<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Url;

class UrlTransformer extends TransformerAbstract
{
	public function transform(Url $url)
	{
		return [
			'id' => $this->id,
			'url' => $this->url,
			'isActive' => ($this->is_active) ? true : false
		];
	}
}