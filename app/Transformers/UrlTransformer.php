<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Url;

class UrlTransformer extends TransformerAbstract
{
	public function transform(Url $url)
	{
		return [
			'id' => $url->id,
			'url' => $url->url,
			'isActive' => ($url->is_active) ? true : false
		];
	}
}