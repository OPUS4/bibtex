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
 * @package     Opus\Bibtex\Import
 * @author      Sascha Szott <opus-repository@saschaszott.de>
 * @copyright   Copyright (c) 2021, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace Opus\Bibtex\Import;

abstract class AbstractMappingConfiguration
{
    const SOURCE_DATA_KEY = 'opus.import.data';
    const SOURCE_DATA_HASH_KEY = 'opus.import.dataHash';
    const HASH_FUNCTION = 'md5';

    // wird z.B. für Auswahl der Mapping-Konfiguration in Administration benötigt
    abstract public function getName();

    abstract public function getDescription();

    abstract public function getRuleList();

    abstract public function prependRule($rule);

    abstract public function appendRule($rule);

    protected function deleteBrace($value)
    {
        if (strlen($value) >= 2 && substr($value, 0, 1) == '{' && substr($value, -1, 1) == '}') {
            $value = substr($value, 1, -1);
        }
        return trim($value);
    }

    protected function extractNameParts($name)
    {
        $name = trim($name);
        $posFirstComma = strpos($name, ',');
        if ($posFirstComma !== false) {
            // Nachname getrennt durch Komma mit Vorname(n)
            // alles nach dem ersten Komma wird hierbei als Vorname interpretiert
            $result = [
                'LastName' => trim(substr($name, 0, $posFirstComma))
            ];
            if ($posFirstComma < strlen($name) - 1) {
                $result['FirstName'] = trim(substr($name, $posFirstComma + 1));
            }
            return $result;
        }

        // mehrere Namensbestandteile sind nicht durch Komma getrennt
        // alles nach dem ersten Leerzeichen wird als Nachname aufgefasst
        // kommt kein Leerzeichen wird, so wurde vermutlich nur der Nachname angegeben
        $posFirstSpace = strpos($name, ' ');
        if ($posFirstSpace === false) {
            return [
                'LastName' => $name
            ];
        }

        $posLastPeriod = strrpos($name, '.');
        if ($posLastPeriod === false) {
            // letztes Zeichen kann kein Leerzeichen sein, daher kein Vergleich der Länge von $name mit $posFirstSpace
            return [
                'FirstName' => trim(substr($name, 0, $posFirstSpace)),
                'LastName' => trim(substr($name, $posFirstSpace + 1))
            ];
        }

        // falls Namensbestandteile abgekürzt werden, so betrachte alles nach dem letzten Punkt als Nachnamen
        $result = [
            'FirstName' => trim(substr($name, 0, $posLastPeriod + 1)),
        ];
        if ($posLastPeriod < strlen($name) - 1) {
            $result['LastName'] = trim(substr($name, $posLastPeriod + 1));
        }
        return $result;
    }
}
