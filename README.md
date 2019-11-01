# Eruption
PHP script for turning FileMaker Pro XML into a directory and file structure.

## Motivation
As your FileMaker Pro solution grows you might find yourself losing overview. I did and I always wished I could use some of the tools most of the other development environments provide. So to have project-wide search, version control, and file comparison, I developed this script that turns an XML of your FileMaker Pro solution into a directory and file structure.

## Not all XML is created equal
As of this writing Eruption can process two types of XML files:

- The newer format that is created by using the **Save a Copy as XML…** command from the **Tools** menu found in FileMaker Pro 18

- The clipboard format for which you need other tools to save as an XML file first

The previous Database Design Report format is not supported.

## Installation

1. Download or clone this repository

2. In your favorite terminal application, change to the project directory. 

3. If you downloaded the .zip file with Safari it should be ```cd ~/Downloads/eruption-master```

4. Make the erupt file executable with ```chmod +x erupt```

## Running

The command line syntax for Eruption is:

```> erupt xml_file output_directory```

**erupt**

Path to the erupt executable found in the project directory

**xml_file**

Path to the XML file generated by FileMaker Pro 18 (or newer)

**output_directory**

Path to the directory where to store the eruption

Example:

```~/Downloads/Eruption/erupt ~/Desktop/filemaker.xml ~/Desktop```