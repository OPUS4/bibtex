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

class Processor
{
    private $mappingConfiguration;

    public function __construct($mappingConfiguration = null)
    {
        // Liste von Regeln für das Mapping der BibTeX-Felder auf die OPUS4-Metddatenfelder
        if (is_null($mappingConfiguration)) {
            $mappingConfiguration = new DefaultMappingConfiguration();
        }
        $this->mappingConfiguration = $mappingConfiguration;
    }

    /**
     * @param $bibtexRecord BibTeX-Record
     * @param $opusMetadata Array mit Metadaten, das mit fromArray in ein Opus-Document umgewandelt werden kann
     * @return array Felder des BibTeX-Records, die bei der Befüllung der Metadaten in $opusMetadata ausgewertet wurden
     */
    public function handleRecord($bibtexRecord, &$opusMetadata)
    {
        $bibtexRecord = array_change_key_case($bibtexRecord);
        $bibtexFieldsEvaluated = [];
        foreach ($this->mappingConfiguration->getRuleList() as $name => $rule) {
            $ruleResult = $rule->apply($bibtexRecord, $opusMetadata);
            if ($ruleResult) {
                // Regel wurde erfolgreich angewendet und das Ziel-Metadatenfeld wurde mit einem Inhalt befüllt
                $fieldsEvaluated = $rule->getEvaluatedBibTexField();
                foreach ($fieldsEvaluated as $fieldEvaluated) {
                    if (substr($fieldEvaluated, 0, 1) !== '_') {
                        // interne Feldnamen des BibTeX-Parsers beginnen mit einem Unterstich und werden hier ignoriert
                        // da sie im Original-BibTeX-Record nicht existieren
                        if (! in_array($fieldEvaluated, $bibtexFieldsEvaluated)) {
                            // TODO wäre es interessant, die Anzahl der Zugriffe auf ein BibTeX-Felds zu protokollieren
                            $bibtexFieldsEvaluated[] = $fieldEvaluated;
                        }
                    }
                }
            }
        }
        return $bibtexFieldsEvaluated;
    }

}
