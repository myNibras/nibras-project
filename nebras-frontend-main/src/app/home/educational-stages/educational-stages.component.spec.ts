import { ComponentFixture, TestBed } from '@angular/core/testing';

import { EducationalStagesComponent } from './educational-stages.component';

describe('EducationalStagesComponent', () => {
  let component: EducationalStagesComponent;
  let fixture: ComponentFixture<EducationalStagesComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [EducationalStagesComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(EducationalStagesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
