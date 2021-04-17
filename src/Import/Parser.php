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

use RenanBr\BibTexParser\Exception\ParserException;
use RenanBr\BibTexParser\Exception\ProcessorException;
use RenanBr\BibTexParser\Listener;
use RenanBr\BibTexParser\Processor\LatexToUnicodeProcessor;

class Parser
{
    private $bibtex;

    /**
     * Parser constructor.
     * @param $bibtex plain BibTeX string or path to BibTeX file
     */
    public function __construct($bibtex)
    {
        $this->bibtex = $bibtex;
    }

    /**
     * @return array
     * @throws \Opus\Bibtex\Import\ParserException
     */
    public function parse()
    {
        $parser = new \RenanBr\BibTexParser\Parser();

        $listener = new Listener();
        $listener->addProcessor(new LatexToUnicodeProcessor()); // behandelt alle Felder (weil leere Blacklist)
        // LTUProcessor schneidet führende und abschließende Leerzeichen in den Feldinhalten ab
        $parser->addListener($listener);

        try {
            if (is_file($this->bibtex)) {
                $parser->parseFile($this->bibtex);
            } else {
                $parser->parseString($this->bibtex);
            }
        } catch (ParserException $e) {
            // Fehler beim Parsen des BibTeX
            throw new \Opus\Bibtex\Import\ParserException();
        } catch (\ErrorException $e) {
            // Fehler beim Einlesen der übergebenen Datei
            throw new \Opus\Bibtex\Import\ParserException();
        }

        try {
            $result = $listener->export();
        } catch (ProcessorException $e) {
            // im Feldinhalt eines Felds befindet sich ein unerwartetes Zeichen
            throw new \Opus\Bibtex\Import\ParserException();
        }
        return $result;
    }

    /**
     * Ermittelt die Namen aller Felder aus dem übergebenen BibTeX-Record. Hierbei werden
     * interne Felder des BibTeX-Parsers ignoriert.
     *
     * @param $bibTexRecord BibTeX-Record
     * @return array Namen der BibTeX-Felder des übergebenen BibTeX-Record
     */
    public function getBibTexFieldNames($bibTexRecord)
    {
        $result = [];
        foreach (array_keys($bibTexRecord) as $fieldName) {
            if (substr($fieldName, 0, 1) === '_') {
                // interne Spezialfelder des BibTeX-Parsers ignorieren
                continue;
            }
            $result[] = $fieldName;
        }
        return $result;
    }
}
