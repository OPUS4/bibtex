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
 * Behandlung von Umlauten, die im BibTeX-File nicht korrekt angegeben wurden (siehe OPUSVIER-4216).
 * Ein Beispiel findet sich in specialchars-invalid.bib
 */
class Umlauts extends ComplexRule
{
    private function convertUmlauts($value)
    {
        if (! preg_match('#"[a, o, u]#i', $value)) {
            return false;
        }
        return str_replace(
            ['"a', '"A', '"o', '"O', '"u', '"U'],
            ['ä', 'Ä', 'ö', 'Ö', 'ü', 'Ü'],
            $value
        );
    }

    protected function setFields($fieldValues, &$documentMetadata)
    {
        foreach ($documentMetadata as $fieldName => $fieldValue) {
            if (is_array($fieldValue)) {
                foreach ($fieldValue as $subFieldIndex => $subFieldValue) {
                    if ($fieldName === 'Enrichment' &&
                        ($subFieldValue['KeyName'] === SourceDataHash::SOURCE_DATA_HASH_KEY ||
                            $subFieldValue['KeyName'] === SourceData::SOURCE_DATA_KEY)) {
                        continue; // der Original-BibTeX-Record soll nicht verändert werden
                    }
                    foreach ($subFieldValue as $name => $value) {
                        $convertedFieldValue = $this->convertUmlauts($value);
                        if ($convertedFieldValue !== false) {
                            $documentMetadata[$fieldName][$subFieldIndex][$name] = $convertedFieldValue;
                        }
                    }
                }
            } else {
                $convertedFieldValue = $this->convertUmlauts($fieldValue);
                if ($convertedFieldValue !== false) {
                    $documentMetadata[$fieldName] = $convertedFieldValue;
                }
            }
        }
    }
}
