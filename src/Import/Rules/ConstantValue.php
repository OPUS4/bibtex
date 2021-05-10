<?php
/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the Cooperative Library Network Berlin-Brandenburg,
 * the Saarland University and State Library, the Saxon State Library -
 * Dresden State and University Library, the Bielefeld University Library and
 * the University Library of Hamburg University of Technology with funding from
 * the German Research Foundation and the European Regional Development Fund.
 *
 * LICENCE
 * OPUS is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or any later version.
 * OPUS is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details. You should have received a copy of the GNU General Public License
 * along with OPUS; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * @category    BibTeX
 * @package     Opus\Bibtex\Import\Rules
 * @author      Sascha Szott <opus-repository@saschaszott.de>
 * @copyright   Copyright (c) 2021, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace Opus\Bibtex\Import\Rules;

/**
 * Eine Regel, um ein Metadatenfeld mit einer Konstante zu befüllen. Hierbei wird der BibTeX-Record nicht ausgewertet.
 */
class ConstantValue implements IRule
{
    /**
     * @var string Name des OPUS-Metadatenfelds
     */
    protected $opusField;

    protected $value;

    /**
     * @return string
     */
    public function getOpusField()
    {
        return $this->opusField;
    }

    /**
     * Setzt den Namen des zu befüllenden OPUS4-Metadatenfelds.
     *
     * @param string $opusField
     */
    public function setOpusField($opusField)
    {
        $this->opusField = ucfirst($opusField);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Setzt den Feldwert für das OPUS4-Metadatenfeld (Konstante).
     *
     * @param $value
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @param array $bibtexRecord
     * @param array $documentMetadata
     * @return bool
     */
    public function apply($bibtexRecord, &$documentMetadata)
    {
        $result = false;
        // der BibTeX-Record wird zur Bestimmung des Metadatenfelds nicht verwendet
        // d.h. Metadatenfeldwert wird hier auf eine Konstante gesetzt oder Bestimmung des Feldinhalts auf Basis
        // von anderen Metadatenfeldern
        if (! is_null($this->value)) {
            $documentMetadata[ucfirst($this->opusField)] = $this->value;
            $result = true;
        }
        return $result;
    }

    public function getEvaluatedBibTexField()
    {
        return [];
    }
}
