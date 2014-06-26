<?php
/**
 * (C) OpenEyes Foundation, 2014
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (C) 2014, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */

namespace services;

use Guzzle\Http\Url;

class FhirContext
{
	/**
	 * @param string $base_url
	 * @return FhirContext
	 */
	static public function create($base_url)
	{
		return new self(Url::factory($base_url));
	}

	private $base_url;

	public function __construct(Url $base_url)
	{
		$this->base_url = $base_url;
	}

	/**
	 * @param string $url
	 * @return string
	 */
	public function canonicaliseUrl($url)
	{
		$url = Url::factory($url);
		if (!$url->isAbsolute()) $url = $this->base_url->combine($url);
		return "$url";
	}
}
