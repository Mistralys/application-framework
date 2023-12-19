<?php

echo sb()
    ->tag('h3', 'Color schemes')
    ->html(UI::systemHint('System-level hint')->makeSystem())
    ->html(UI::systemHint('Success-level hint')->makeSuccess())
    ->html(UI::systemHint('Developer-level hint')->makeDeveloper());
