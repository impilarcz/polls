# Polls

[![Build Status](https://img.shields.io/travis/nextcloud/polls.svg?style=flat-square)](https://travis-ci.org/nextcloud/polls)
[![Scrutinizer](https://img.shields.io/scrutinizer/g/nextcloud/polls.svg?style=flat-square)](https://scrutinizer-ci.com/g/nextcloud/polls)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/nextcloud/polls.svg?style=flat-square)](https://scrutinizer-ci.com/g/nextcloud/polls)
[![Software License](https://img.shields.io/badge/license-AGPL-brightgreen.svg?style=flat-square)](LICENSE)

This is a poll app, similar to doodle or dudle, for Nextcloud written in PHP and JS / Vue.

## Features
- :bar_chart: Create / edit polls (datetimes and texts)
- :date: Set an expiration date
- :lock: Restrict access (all site users or invited users only)
- :speech_balloon: Comments
- Create public polls
- Invite users, groups and contacts (directly or via circles or contact groups)
- Hide results until the poll is closed
- Create anonymised polls (participants names get pseudonymized for other users)

## Bugs
- https://github.com/nextcloud/polls/issues

## Screenshots
Create a new poll from the navigation bar and get an overview of your polls
![Overview](screenshots/overview.png)

#### Vote and comment
![Vote](screenshots/vote.png)

#### Edit poll inside the vote page
![Edit poll](screenshots/edit-poll.png)
![Edit options](screenshots/options.png)

#### Add shared links to your poll
![Share poll](screenshots/shares.png)

#### View the vote page on mobiles
![Vote mobile portrait](screenshots/mobile-portrait.png)

## Installation / Update
This app is supposed to work on Nextcloud version 17+.

### Install latest release
You can download and install the latest release from the [Nextcloud app store](https://apps.nextcloud.com/apps/polls).

### Install from git
If you want to run the latest development version from git source, you need to clone the repo to your apps folder:

```
git clone https://github.com/nextcloud/polls.git
```

* Install dev environment with ```make dev-setup```
* Compile polls.js with ```npm run build```
* Run a complete build with ```make all``` (installs dev env, runs linter and builds the polls.js)

## Contribution Guidelines
Please read the [Code of Conduct](https://nextcloud.com/community/code-of-conduct/). This document offers some guidance
to ensure Nextcloud participants can cooperate effectively in a positive and inspiring atmosphere, and to explain how together
we can strengthen and support each other.

For more information please review the [guidelines for contributing](https://github.com/nextcloud/server/blob/master/.github/CONTRIBUTING.md) to this repository.
