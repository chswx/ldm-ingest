# @chswx LDM Parser

[![Build Status](https://travis-ci.org/jaredwsmith/ldm-ingest.svg?branch=master)](https://travis-ci.org/chswx/ldm-ingest) (test suite in progress, don't read much into this yet!)

A series of scripts to ingest and relay NWS watch/warning/advisory products via Unidata’s LDM. Will eventually expand to forecast and METAR processing as well; baby steps for now, though.

## What’s done

- VTEC (Valid Time Extent Code) awareness
- Rudimentary Twitter support
- Relays for most common hazardous weather advisories

## What’s in the works

- A variety of bug fixes to VTEC support
- Support for watch probabilities (WWUS40)
- Support for additional output endpoints
- Storm-based warnings via PostGIS polygon queries
- Tornado/flash flood emergency detection
- Impact-based warnings (TORNADO…HAIL…WIND tags)
- Moving to a totally pub/sub architecture
- Awareness of conditions via METAR ingest
- Awareness of forecast updates

## Pie in the sky stuff

- Configurable UI
- Portable installation

## See it in action

Follow [@chswx on Twitter](http://twitter.com/chswx) during inclement weather in Charleston, SC to see examples of the bot’s output. 

## Questions?

File issues on GitHub against this project or contact me on Twitter: [@jaredwsmith](http://twitter.com/jaredwsmith)

## Licensing, warranty, etc.

Licensing TBD. Code is pretty lousy and should not be used for life and death applications. No warranty implied. Use at your own risk.

## Many Thanks

Many thanks to @blairblends, @edarc and @UpdraftNetworks for infrastructure and seeding the initial code for this project.
