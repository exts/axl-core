# Axl Roadmap

## Overview
Axl is a PHP library providing a universal abstraction layer for AI APIs. This roadmap outlines our development milestones from initial alpha to stable release.

---

## Version 0.1.0-alpha.1 (First Release)
**Goal:** Core functionality with multi-getProvider support

### ✅ Completed
- [x] OpenAI GPT-4.1-mini Chat Completions support
- [x] OpenAI GPT-5.1-codex-mini Response API support
- [x] Response parsing (TextResponse)
- [x] Class based Response Object Mapping
- [x] Model registration system
- [x] Builder pattern API
- [x] Context → Response pipeline
- [x] Claude Opus/Sonnet/Haiku support
- [x] Streaming support with PHP generators
  ```php
  $stream = $ai->stream(new TextContext(
    Messages::system(new Text("You are a file analyzer")),
    Messages::user(
        new Text("Describe this file"),
        new FileToBase64(__DIR__ . '/document-example.pdf', ['filename' => 'document-example.pdf', ])
    )),
  );
  
  foreach($stream as $resp) {
      echo $resp;
  }
  ```

### ⏳ In Progress
- [x] Google Gemini Nano Banana support
- [x] Finish parsing flattened Content
- Tests completed

### ⏳ Up Next

### 📋 Requirements for Release
- All few major providers working (OpenAI, Claude, Gemini)
- Streaming implemented and tested
- Basic documentation
- file upload support with fileId support
- audio bridge support
  - Transcribe separated from tts
  - Speech (tts) separated from transcribe
- Tests completed