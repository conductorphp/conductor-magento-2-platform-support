<?php

namespace ConductorMagento2PlatformSupport;

// This module registers its strategies under its own platform key in ConfigProvider and never under
// the application-orchestration interface names. Binding the interfaces here is what made platform
// modules mutually exclusive: last ConfigProvider won (CTAP-1757). The strategy classes are built by
// core's reflection-based abstract factory.
return [];
