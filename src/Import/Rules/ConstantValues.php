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
 * @copyright   Copyright (c) 2021, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 *
 * @category    BibTeX
 * @package     Opus\Bibtex\Import\Rules
 * @author      Sascha Szott <opus-repository@saschaszott.de>
 */

namespace Opus\Bibtex\Import\Rules;

use function class_exists;
use function method_exists;
use function ucfirst;

/**
 * Eine Regel, um mehrere OPUS-Metadatenfelder mit Konstanten zu befüllen. Hierbei wird der Inhalt des zu verarbeitenden
 * BibTeX-Record nicht ausgewertet.
 */
class ConstantValues implements RuleInterface
{
    /**
     * beschreibt die zu setzenden OPUS-Felder und die dabei zu nutzenden Konstanten
     *
     * @var array
     */
    private $options;

    /**
     * Erlaubt das Setzen der zu setzenden OPUS-Felder sowie der dabei zu verwendenden Werte (Konstanten).
     *
     * @param array $options
     * @return $this
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Ausführung der konfigurierten Regel zur Befüllung von OPUS-Metadatenfeldern mit konstanten Werten.
     *
     * @param array $bibtexRecord BibTeX-Record (Array von BibTeX-Feldern)
     * @param array $documentMetadata OPUS-Metadatensatz (Array von Metadatenfeldern)
     * @return bool liefert true, wenn die Regel erfolgreich angewendet werden konnte
     */
    public function apply($bibtexRecord, &$documentMetadata)
    {
        $result = false;

        // der BibTeX-Record wird zur Bestimmung des Metadatenfelds nicht verwendet
        if ($this->options !== null) {
            foreach ($this->options as $propName => $propValue) {
                $propName  = ucfirst($propName);
                $className = 'Opus\Bibtex\Import\Rules\\' . $propName;
                if (class_exists($className) && method_exists($className, 'setValue')) {
                    // zu setzender Wert wird durch Ausführung der Regelklasse bestimmt
                    $class = new $className();
                    $class->setValue($propValue);
                    $class->apply($bibtexRecord, $documentMetadata);
                } else {
                    $documentMetadata[ucfirst($propName)] = $propValue;
                }
                $result = true;
            }
        }
        return $result;
    }

    /**
     * Liefert die Liste der ausgewerteten BibTeX-Felder.
     *
     * @return array
     */
    public function getEvaluatedBibTexField()
    {
        return [];
    }
}
