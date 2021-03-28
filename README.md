# @chswx LDM Parser

[![Build Status](https://travis-ci.org/chswx/ldm-ingest.svg?branch=master)](https://travis-ci.org/chswx/ldm-ingest) (test suite in progress, don't read much into this yet!)

A series of scripts to ingest and store NWS watch/warning/advisory products via Unidata’s LDM. Will eventually expand to forecast and METAR processing as well; baby steps for now, though.

## New in 2.0

All responsibilities for outputting the results of parsing out products from LDM will be placed on the [Alerter](http://github.com/chswx/alerter) going forward. Thus, a lot of what's new in 2.0 has been more code _deletion_ than anything else.
Version 2.0, instead of handling the entire lifecycle of a request from receiving it from LDM to sending it out over Twitter, will just dump everything in a pub-sub-aware database (currently RethinkDB) and let other worker processes figure it out. This should improve performance, scalability, and redundancy quite nicely. We store things in JSON; this makes it easy to send the data to virtually anywhere, including directly over a socket into a Web browser. (Wink
wink.)

## What’s done

- VTEC (Valid Time Extent Code) awareness
- JSON output
- Impact-based warnings
- Storm-based warnings
- Pub/sub architecture based on RethinkDB

## What’s in the works

- Support for parsing Special Weather Statements/Significant Weather Advisories (#13)
- Support for watch probabilities (WWUS40) (#14)
- Parsing and geocoding Mesoscale Convective/Precip Discussions from SPC and WPC (#15)
- SPC outlooks (#16)
- LSRs (#17)
- Tropical advisories (#18)
- Climate products (#23)
- Awareness of conditions via METAR ingest
- Awareness of forecast updates

## Pie in the sky stuff

- Configurable UI
- Portable installation

## See it in action

The current version of the LDM ingestor works behind the scenes.

To see its [predecessor](https://github.com/chswx/ldm-ingest-legacy) in action, follow [@chswx on Twitter](http://twitter.com/chswx) during inclement weather in Charleston, SC to see examples of the bot’s output.

## Questions?

File issues on GitHub against this project or contact me on Twitter: [@jaredwsmith](http://twitter.com/jaredwsmith)

## Licensing, warranty, etc.

Licensing TBD. Not for use in life or death situations; code is getting better but the level of rigor is below what I would expect for mission-critical things.

## Many Thanks

Many thanks to @blairblends, @edarc and @chswx\LDMIngest for infrastructure and seeding the initial code for this project.
