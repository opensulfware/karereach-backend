# KareReach AI — Future Roadmap (V2 & V3)

This document outlines the planned evolution of the KareReach AI platform beyond the MVP.

## Version 2.0 — Enhanced Intelligence & Supervision
*Focus: Scaling oversight and improving clinical accuracy.*

### Backend
- **Supervisor Role**: Implement RBAC for regional supervisors to monitor CHW activity.
- **Clinical Feedback Loop**: API for supervisors to annotate AI results, feeding back into prompt tuning.
- **Follow-up Tracking**: New endpoints to record patient outcomes (recovered, referred, etc.).
- **Aggregate Analytics**: Regional dashboards providing risk distribution and consultation volume.
- **Push Notifications**: FCM integration for urgent case alerts and supervisor feedback.
- **Object Storage**: Migration to S3-compatible storage (AWS/MinIO) for image management.
- **Webhooks**: Outbound events for high-risk cases to external health systems.
- **Audit Logging**: Comprehensive action tracking for regulatory compliance.

---

## Version 3.0 — Health System Integration
*Focus: Interoperability and population health intelligence.*

### Backend
- **FHIR R4 Compliance**: Expose data via FHIR-compatible endpoints for DHIS2/OpenMRS integration.
- **Multi-Model Support**: Support for additional models (Llama 3 Med, BioMistral) with region-specific selection.
- **Multi-Turn AI Dialogue**: Support for follow-up clinical questions via the AI result interface.
- **Treatment Protocols**: Integration with WHO essential medicines list for dosage guidance.
- **Epidemic Detection**: Automated cluster detection based on aggregate symptom patterns.
- **Multi-Tenancy**: Support for isolated health programs/NGOs on a single backend instance.
- **API Versioning**: Formal version management (v1, v2, v3) for long-term client support.

---
*Built by [OpenSulfware](https://github.com/opensulfware)*
