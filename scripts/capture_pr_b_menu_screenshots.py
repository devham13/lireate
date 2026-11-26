#!/usr/bin/env python3
"""Thin wrapper for capture_pr_b_public_menus_screenshots.py."""

import runpy
import sys
from pathlib import Path

TARGET = Path(__file__).resolve().parent / "capture_pr_b_public_menus_screenshots.py"
sys.exit(runpy.run_path(str(TARGET), run_name="__main__") or 0)